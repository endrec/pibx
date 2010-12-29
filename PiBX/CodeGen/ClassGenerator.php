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
require_once 'PiBX/AST/Tree.php';
require_once 'PiBX/AST/Collection.php';
require_once 'PiBX/AST/CollectionItem.php';
require_once 'PiBX/AST/Enumeration.php';
require_once 'PiBX/AST/EnumerationValue.php';
require_once 'PiBX/AST/Structure.php';
require_once 'PiBX/AST/StructureElement.php';
require_once 'PiBX/AST/StructureType.php';
require_once 'PiBX/AST/Type.php';
require_once 'PiBX/AST/TypeAttribute.php';
require_once 'PiBX/AST/Visitor/VisitorAbstract.php';
require_once 'PiBX/Binding/Names.php';
/**
 * Generating the PHP-code of the classes is done here, with a Hierarchical Visitor
 * of the AST.
 * 
 * After visiting, the code can be retrieved with <code>getClasses()</code>.
 * So, the actual file writing has to be done separately.
 *
 * @author Christoph Gockel
 */
class PiBX_CodeGen_ClassGenerator implements PiBX_AST_Visitor_VisitorAbstract {
    private $xml;
    private $xsdBaseTypes = array('string', 'long', 'date');

    /**
     * @var string[] hash with generated class-code
     */
    private $classes;
    private $currentClass;
    private $currentClassName;
    private $currentClassAttributes;
    private $currentClassMethods;
    /**
     * @var string Used for additional class-code. Content will be added after the closing "}".
     */
    private $classAppendix;
    
    public function  __construct() {
        $this->classes = array();

        $this->currentClassName = '';
        $this->currentClass = '';
        $this->currentClassAttributes = '';
        $this->currentClassMethods = '';
        $this->classAppendix = '';
    }

    /**
     * 
     * @return string[] hash with class-name => class-code
     */
    public function getClasses() {        
        return $this->classes;
    }


    public function visitCollectionEnter(PiBX_AST_Tree $tree) {
        $name = $tree->getParent()->getName();
        
        $this->currentClassAttributes .= "\tprivate \$" . $name . ";\n";
        
        $setter = PiBX_Binding_Names::createSetterNameFor($tree);
        $getter = PiBX_Binding_Names::createGetterNameFor($tree);
        
        $this->currentClassMethods .= "\tpublic function " . $setter . "(\$a) {\n"
                                    . "\t\t\$this->" . $name . " = \$a;\n"
                                    . "\t}\n"
                                    . "\tpublic function " . $getter . "() {\n"
                                    . "\t\treturn \$this->" . $name . ";\n"
                                    . "\t}\n";
        
        return true;
    }
    public function visitCollectionLeave(PiBX_AST_Tree $tree) {
        return true;
    }

    public function visitCollectionItem(PiBX_AST_Tree $tree) {
        if ($tree->getParent()->countChildren() == 1) {
            if (in_array($tree->getXsdType(), $this->xsdBaseTypes)) {
                $this->xml .= '<value style="element" name="'.$tree->getName().'" type="'.$tree->getXsdType().'"/>';
            } else {
                $this->xml .= '<structure map-as="'.$tree->getXsdType().'" name="'.$tree->getName().'"/>';
            }
        } else {
            //$this->xml .= '<structure map-as="" name="'.$tree->getName().'"/>';
        }
        
        return true;
    }
    
    public function visitEnumerationEnter(PiBX_AST_Tree $tree) {
        $enumName = $tree->getName();

        if ($tree->getParent() == null) {
            // at the moment separate enums are not supported, yet.
            //$this->classAppendix .= 'class b_' . $this->currentClassName . '_' . ucfirst($enumName) . " {\n";
            return false;
        }

        $attributeName = strtolower($tree->getName());
        $methodName = ucfirst(strtolower($tree->getName()));

        $this->currentClassAttributes .= "\tprivate \$".$attributeName.";\n";
        $methods = "\tpublic function set".$methodName."(\$".$attributeName.") {\n"
                 . "\t\t\$this->".  strtolower($tree->getName())." = \$".$attributeName.";\n"
                 . "\t}\n"
                 . "\tpublic function get".$methodName."() {\n"
                 . "\t\treturn \$this->".  strtolower($tree->getName()).";\n"
                 . "\t}\n";

        $this->currentClassMethods .= $methods;

        return true;
    }
    public function visitEnumerationLeave(PiBX_AST_Tree $tree) {
        if ($tree->getParent() == null) {
            //$this->classAppendix .= "}";
            return false;
        }
        return true;
    }
    public function visitEnumeration(PiBX_AST_Tree $tree) {
    }

    public function visitEnumerationValue(PiBX_AST_Tree $tree) {
    }
    
    public function visitStructureEnter(PiBX_AST_Tree $tree) {
        $structureType = $tree->getType();
        
        if ($structureType === PiBX_AST_StructureType::CHOICE()) {
            $name = $tree->getParent()->getName();
            $attributeName = $name . 'Select';
            $this->currentClassAttributes .= "\tprivate \$" . $attributeName . " = -1;\n";
            
            $constantNames = PiBX_Binding_Names::createChoiceConstantsFor($tree);
            $i = 0;
            foreach ($constantNames as $constant) {
                $this->currentClassAttributes .= "\tprivate \${$constant} = $i;\n";
                ++$i;
            }

            $methodName = ucfirst($attributeName);
            $methods = "\tprivate function set{$methodName}(\$choice) {\n"
                     . "\t\tif (\$this->{$attributeName} == -1) {\n"
                     . "\t\t\t\$this->{$attributeName} = \$choice;\n"
                     . "\t\t} elseif (\$this->{$attributeName} != \$choice) {\n"
                     . "\t\t\tthrow new RuntimeException('Need to call clear{$methodName}() before changing existing choice');\n"
                     . "\t\t}\n"
                     . "\t}\n";

            $methods .= "\tpublic function clear{$methodName}() {\n"
                      . "\t\t\$this->{$attributeName} = -1;\n"
                      . "\t}\n";

            $this->currentClassMethods .= $methods;
        }
        
        return true;
    }
    public function visitStructureLeave(PiBX_AST_Tree $tree) {
        if ($tree->getType() == PiBX_AST_StructureType::CHOICE()) {
            $this->xml .= '</structure>';
        }
        $this->xml .= "</structure>";
        return true;
    }
    
    public function visitStructureElementEnter(PiBX_AST_Tree $tree) {
        $name = $tree->getName();
        $parentName = $tree->getParent()->getName();

        $attributeName = $parentName . $name;
        
        $selectName = ucfirst($parentName) . 'Select';
        
        $this->currentClassAttributes .= "\tprivate \$" . $attributeName . ";\n";
        
        $choiceConstant = $parentName . '_' . $name . '_CHOICE';
        $choiceConstant = strtoupper($choiceConstant);

        $setter = PiBX_Binding_Names::createSetterNameFor($tree);
        $getter = PiBX_Binding_Names::createGetterNameFor($tree);

        $methodName = ucfirst($attributeName);

        $methods = "\tpublic function if{$methodName}() {\n"
                . "\t\treturn \$this->{$parentName}Select == \$this->$choiceConstant;\n"
                . "\t}\n";
        $methods .= "\tpublic function {$setter}(\${$attributeName}) {\n"
                  . "\t\t\$this->set{$selectName}(\$this->{$choiceConstant});\n"
                  . "\t\t\$this->{$attributeName} = \${$attributeName};\n"
                  . "\t}\n";
        $methods .= "\tpublic function {$getter}() {\n"
                  . "\t\treturn \$this->{$attributeName};\n"
                  . "\t}\n";

        $this->currentClassMethods .= $methods;
        
        return true;
    }
    public function visitStructureElementLeave(PiBX_AST_Tree $tree) {
        return true;
    }
    
    public function visitTypeEnter(PiBX_AST_Tree $tree) {
        $this->currentClassName = PiBX_Binding_Names::createClassnameFor($tree);
        $this->currentClass = 'class ' . $this->currentClassName . " {\n";
        
        return true;
    }
    public function visitTypeLeave(PiBX_AST_Tree $tree) {
        $this->currentClass .= $this->currentClassAttributes;
        $this->currentClass .= "\n";
        $this->currentClass .= $this->currentClassMethods;
        $this->currentClass .= '}';
        if (trim($this->classAppendix) != '') {
            $this->currentClass .= "\n";
            $this->currentClass .= $this->classAppendix;
        }
        $this->classes[$this->currentClassName] = $this->currentClass;

        $this->currentClassAttributes = '';
        $this->currentClassMethods = '';
        $this->currentClass = '';
        $this->classAppendix = '';
        return true;
    }

    public function visitTypeAttributeEnter(PiBX_AST_Tree $tree) {
        if ($tree->countChildren() == 0) {
            // base type attribute
            $attributeName = strtolower($tree->getName());
            $methodName = ucfirst(strtolower($tree->getName()));
            
            $this->currentClassAttributes .= "\tprivate \$".$attributeName.";\n";
            $methods = "\tpublic function set".$methodName."(\$".$attributeName.") {\n"
                     . "\t\t\$this->".  strtolower($tree->getName())." = \$".$attributeName.";\n"
                     . "\t}\n"
                     . "\tpublic function get".$methodName."() {\n"
                     . "\t\treturn \$this->".  strtolower($tree->getName()).";\n"
                     . "\t}\n";

            $this->currentClassMethods .= $methods;

            return false;
        } else {
            return true;
        }
    }
    public function visitTypeAttributeLeave(PiBX_AST_Tree $tree) {
        return true;
    }
}
