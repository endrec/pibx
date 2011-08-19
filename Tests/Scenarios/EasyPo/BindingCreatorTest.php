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
require_once 'PiBX/CodeGen/ASTCreator.php';
require_once 'PiBX/CodeGen/ASTOptimizer.php';
require_once 'PiBX/CodeGen/SchemaParser.php';
require_once 'PiBX/Binding/Creator.php';
/**
 * Testing the BindingCreator in scenario "EasyPO".
 *
 * @author Christoph Gockel
 */
class PiBX_Scenarios_EasyPO_BindingCreatorTest extends PHPUnit_Framework_TestCase {
    public function testEasyPoBinding() {
        $filepath = dirname(__FILE__) . '/../../_files/EasyPO/';
        $schemaFile = $filepath . '/easypo.xsd';
        $schema = simplexml_load_file($schemaFile);
        $bindingFile = file_get_contents($filepath . '/binding.xml');
        
        $typeUsage = new PiBX_CodeGen_TypeUsage();

        $parser = new PiBX_CodeGen_SchemaParser($schemaFile, $typeUsage);
        $parsedTree = $parser->parse();

        $creator = new PiBX_CodeGen_ASTCreator($typeUsage);
        $parsedTree->accept($creator);

        $typeList = $creator->getTypeList();

        $usages = $typeUsage->getTypeUsages();

        $optimizer = new PiBX_CodeGen_ASTOptimizer($typeList, $typeUsage);
        $typeList = $optimizer->optimize();

        $b = new PiBX_Binding_Creator();

        foreach ($typeList as &$type) {
            $type->accept($b);
        }

        $this->assertEquals($bindingFile, $b->getXml());
    }
}
