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
require_once './PiBX/CodeGen.php';

/**
 * Class autoloader.
 * @author Endre Czirbesz
 * @param string $class_name 
 */
function __autoload($class_name) {
    if (preg_match('|PiBX_ParseTree_[A-Z][a-zA-Z]+Node|', $class_name)) {
        //print $class_name . " will be loaded.\n";
        require_once strtr($class_name, '_', '/') . '.php';
    }
    if (!class_exists($class_name, false)) {
        print "Error: Class not found: $class_name";
        print "Current directory: " . getcwd() . "\n";
        exit(1);
    }
}

/**
 * CodeGen is a command-line interface for PiBX_CodeGen.
 *
 * @author Christoph Gockel
 */
print "PiBX - CodeGen\n";

$options = array();

for ($i = 2; $i < $argc; $i++) {
    $value = $argv[$i];
    
    if ($value == '--typechecks') {
        $options['typechecks'] = true;
    }
}

$c = new PiBX_CodeGen($argv[1], $options);
