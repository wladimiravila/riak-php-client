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

namespace Basho\Tests\Riak;

use Basho\Riak\Node;
use Basho\Tests\TestCase;

/**
 * Main class for testing Riak clustering
 *
 * @author Christopher Mancini <cmancini at basho d0t com>
 */
class NodeTest extends TestCase
{
    /**
     * @dataProvider getLocalNode
     *
     * @param $node Node
     */
    public function testConfig($node)
    {
        $this->assertEquals(static::getTestHost(), $node->getHost());
        $this->assertEquals(static::getTestPort(), $node->getPort());
        $this->assertNotEmpty($node->getSignature());
    }
}
 