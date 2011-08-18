<?php
/**
 * Copyright (c) 2010, Christoph Gockel.
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without modification,
 * are permitted provided that the following conditions are met:
 *
 * * Redistributions of source code must retain the above copyright notice, this
 *   list of conditions and the following disclaimer.
 * * Redistributions in binary form must reproduce the above copyright notice,
 *   this list of conditions and the following disclaimer in the documentation
 *   and/or other materials provided with the distribution.
 * * Neither the name of PiBX nor the names of its contributors may be used
 *   to endorse or promote products derived from this software without specific
 *   prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
 * ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR
 * ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
 * (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON
 * ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
 * SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */
require_once dirname(__FILE__) . '/../../bootstrap.php';
require_once 'PHPUnit/Framework.php';
require_once 'Tests/Scenarios/EasyPo/ASTCreatorTest.php';
require_once 'Tests/Scenarios/EasyPo/BindingCreatorTest.php';
require_once 'Tests/Scenarios/EasyPo/ClassGeneratorTest.php';
require_once 'Tests/Scenarios/EasyPo/MarshallerTest.php';
require_once 'Tests/Scenarios/EasyPo/SchemaParserTest.php';
require_once 'Tests/Scenarios/EasyPo/UnmarshallerTest.php';
/**
 * Test-Suite of package "Runtime".
 *
 * @author Christoph Gockel
 */
class PiBX_Scenarios_EasyPO_Suite extends PHPUnit_Framework_TestSuite {

    public static function suite() {
        $suite = new PHPUnit_Framework_TestSuite();
        
        $suite->addTestSuite('PiBX_Scenarios_EasyPO_ASTCreatorTest');
        $suite->addTestSuite('PiBX_Scenarios_EasyPO_BindingCreatorTest');
        $suite->addTestSuite('PiBX_Scenarios_EasyPO_ClassGeneratorTest');
        $suite->addTestSuite('PiBX_Scenarios_EasyPO_MarshallerTest');
        $suite->addTestSuite('PiBX_Scenarios_EasyPO_SchemaParserTest');
        $suite->addTestSuite('PiBX_Scenarios_EasyPO_UnmarshallerTest');
        
        return $suite;
    }
}
