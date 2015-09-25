<?php

/*
Copyright 2015 Basho Technologies, Inc.

Licensed to the Apache Software Foundation (ASF) under one or more contributor license agreements.  See the NOTICE file
distributed with this work for additional information regarding copyright ownership.  The ASF licenses this file
to you under the Apache License, Version 2.0 (the "License"); you may not use this file except in compliance
with the License.  You may obtain a copy of the License at

  http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software distributed under the License is distributed on an
"AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.  See the License for the
specific language governing permissions and limitations under the License.
*/

namespace Basho\Tests;

use Basho\Riak;
use Basho\Riak\Command;

/**
 * Functional tests related to Counter CRDTs
 *
 * @author Christopher Mancini <cmancini at basho d0t com>
 */
class MapReduceOperationsTest extends TestCase
{
    protected static $mr_content = [
        'p0' => "Alice was beginning to get very tired of sitting by her sister on the bank, and of having nothing to do: once or twice she had peeped into the book her sister was reading, but it had no pictures or conversations in it, 'and what is the use of a book,' thought Alice 'without pictures or conversation?'",
        'p1' => "So she was considering in her own mind (as well as she could, for the hot day made her feel very sleepy and stupid), whether the pleasure of making a daisy-chain would be worth the trouble of getting up and picking the daisies, when suddenly a White Rabbit with pink eyes ran close by her.",
        'p2' => "The rabbit-hole went straight on like a tunnel for some way, and then dipped suddenly down, so suddenly that Alice had not a moment to think about stopping herself before she found herself falling down a very deep well."
    ];

    public static function setUpBeforeClass()
    {
        $node = [
            (new Riak\Node\Builder)
                ->atHost(static::getTestHost())
                ->onPort(static::getTestPort())
                ->build()
        ];

        $riak = new Riak($node);

        foreach (static::$mr_content as $key => $value) {
            $command = (new Command\Builder\StoreObject($riak))
                ->buildObject($value)
                ->buildLocation($key, 'phptest_mr')
                ->build();

            $command->execute();
        }
    }

    public static function tearDownAfterClass()
    {
        $node = [
            (new Riak\Node\Builder)
                ->atHost(static::getTestHost())
                ->onPort(static::getTestPort())
                ->build()
        ];

        $riak = new Riak($node);

        foreach (static::$mr_content as $key => $object) {
            $command = (new Command\Builder\DeleteObject($riak))
                ->buildLocation($key, 'phptest_mr')
                ->build();

            $command->execute();
        }
    }

    /**
     * @dataProvider getLocalNodeConnection
     *
     * @param $riak \Basho\Riak
     */
    public function testFetch($riak)
    {
        $command = (new Command\Builder\MapReduce\FetchObjects($riak))
            ->addBucketInput(new Riak\Bucket('phptest_mr'))
            ->buildMapPhase('', '',
                "function(v) {var m = v.values[0].data.toLowerCase().match(/[A-Za-z]*/g); var r = []; for(var i in m) {if(m[i] != '') {var o = {};o[m[i]] = 1;r.push(o);}}return r;}")
            ->buildReducePhase('', '',
                "function(v) {var r = {};for(var i in v) {for(var w in v[i]) {if(w in r) r[w] += v[i][w]; else r[w] = v[i][w];}}return [r];}")
            ->build();

        $response = $command->execute();

        $this->assertEquals('200', $response->getCode(), $response->getMessage());

        $results = $response->getResults();
        $this->assertEquals(8, $results[0]->the);
    }
}