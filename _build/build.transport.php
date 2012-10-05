<?php
/**
 * Eway
 * 
 * Copyright 2012 by Everett Griffiths <everett@craftsmancoding.com>
 *
 * This is an Extra for MODX 2.2.x.
 *
 * Eway is free software; you can redistribute it and/or modify it under the
 * terms of the GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the License, or (at your option) any later
 * version.
 *
 * Eway is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR
 * A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with
 * Quip; if not, write to the Free Software Foundation, Inc., 59 Temple Place,
 * Suite 330, Boston, MA 02111-1307 USA
 *
 * Eway build script.  
 *
 * NOTICE TO DEVELOPERS:
 * This extra was built on an installation of MODX where the core directory was
 * inside the public document root.  If your installation of MODX uses a different
 * location, adjust the MODX_CORE_PATH.
 *
 * The Git directory was installed inside assets/components/eway.
 *
 * @package eway
 * @subpackage build
 */



// The deets...
define('PKG_NAME', 'Eway');
define('PKG_NAME_LOWER', strtolower(PKG_NAME));
define('PKG_VERSION', '0.1');
define('PKG_RELEASE', 'beta');

if (!defined('MODX_CORE_PATH')) {
	define('MODX_CORE_PATH', dirname(dirname(dirname(dirname(dirname(__FILE__))))).'/core/');
}
if (!defined('MODX_CONFIG_KEY')) {
	define('MODX_CONFIG_KEY', 'config');
}

 
// Start the stopwatch...
$mtime = microtime();
$mtime = explode(' ', $mtime);
$mtime = $mtime[1] + $mtime[0];
$tstart = $mtime;
// Prevent global PHP settings from interrupting
set_time_limit(0); 

echo 'Creating Package...';

// fire up MODX
require_once( MODX_CORE_PATH . 'model/modx/modx.class.php');
$modx = new modx();
$modx->initialize('mgr');
$modx->setLogLevel(modX::LOG_LEVEL_INFO);
$modx->setLogTarget('ECHO'); echo '<pre>'; 
flush();

$modx->loadClass('transport.modPackageBuilder', '', false, true);
$builder = new modPackageBuilder($modx);
$builder->createPackage(PKG_NAME_LOWER, PKG_VERSION, PKG_RELEASE);
$builder->registerNamespace(PKG_NAME_LOWER, false, true, '{core_path}components/' . PKG_NAME_LOWER.'/');

// Get the object to be packaged
$Snippet = $modx->newObject('modSnippet');
$Snippet->fromArray(array(
    'name' => 'EwaySharedPayments',
    'description' => '<strong>Version '.PKG_VERSION.'-'.PKG_RELEASE.'</strong> Integrates the Eway Shared Payments API',
    'snippet' => file_get_contents('../core/components/eway/elements/snippets/snippet.EwaySharedPayments.php'),
));


$Category = $modx->newObject('modCategory');
$Category->set('category', PKG_NAME);

$Snippet->addOne($Category);

$attributes = array(
	xPDOTransport::UNIQUE_KEY => 'name',
	xPDOTransport::PRESERVE_KEYS => false,
	xPDOTransport::UPDATE_OBJECT => true,
    xPDOTransport::RELATED_OBJECTS => true,
    xPDOTransport::RELATED_OBJECT_ATTRIBUTES => array (
        'Category' => array(
            xPDOTransport::PRESERVE_KEYS => true,
            xPDOTransport::UPDATE_OBJECT => false,
            xPDOTransport::UNIQUE_KEY => array('category'),
        ),
    ),
	
);

$vehicle = $builder->createVehicle($Snippet, $attributes);

// Copy over related files... may as well attach them to the Snippet
$vehicle->resolve('file',array(
    'source' => MODX_ASSETS_PATH .'components/'.PKG_NAME_LOWER.'/assets/components/'.PKG_NAME_LOWER,
    'target' => "return MODX_ASSETS_PATH . 'components/';",
));


// Insert the vehicle into the transport package 
$builder->putVehicle($vehicle);


// Create System Setting
$Setting = $modx->newObject('modSystemSetting');
$Setting->fromArray(array(
    'key' => 'eway.customerID',
    'value' => '',
    'xtype' => 'textfield',
    'namespace' => 'eway',
    'area' => 'default',
),'',true,true);
$vehicle = $builder->createVehicle($Setting, $attributes);
$builder->putVehicle($vehicle);


// Create Chunks
$Chunk = $modx->newObject('modChunk');
$Chunk->fromArray(array(
    'name' => 'eway_error',
    'description' => 'Formatting Chunk for Eway errors. <strong>DO NOT MODIFY</strong>. Please make a copy of this Chunk and reference it in your &errorTpl parameter',
    'snippet' => file_get_contents('../core/components/eway/elements/chunks/eway_error.html'),
));

$Chunk->addOne($Category);

$attributes = array(
	xPDOTransport::UNIQUE_KEY => 'name',
	xPDOTransport::PRESERVE_KEYS => false,
	xPDOTransport::UPDATE_OBJECT => true,
    xPDOTransport::RELATED_OBJECTS => true,
    xPDOTransport::RELATED_OBJECT_ATTRIBUTES => array (
        'Category' => array(
            xPDOTransport::PRESERVE_KEYS => true,
            xPDOTransport::UPDATE_OBJECT => false,
            xPDOTransport::UNIQUE_KEY => array('category'),
        ),
    ),
	
);

$vehicle = $builder->createVehicle($Chunk, $attributes);
$builder->putVehicle($vehicle);


$Chunk = $modx->newObject('modChunk');
$Chunk->fromArray(array(
    'name' => 'eway_error',
    'description' => 'Sample Eway Form. <strong>DO NOT MODIFY</strong>. Please make a copy of this Chunk.',
    'snippet' => file_get_contents('../core/components/eway/elements/chunks/eway_sample_form.html'),
));

$Chunk->addOne($Category);

$attributes = array(
	xPDOTransport::UNIQUE_KEY => 'name',
	xPDOTransport::PRESERVE_KEYS => false,
	xPDOTransport::UPDATE_OBJECT => true,
    xPDOTransport::RELATED_OBJECTS => true,
    xPDOTransport::RELATED_OBJECT_ATTRIBUTES => array (
        'Category' => array(
            xPDOTransport::PRESERVE_KEYS => true,
            xPDOTransport::UPDATE_OBJECT => false,
            xPDOTransport::UNIQUE_KEY => array('category'),
        ),
    ),
	
);

$vehicle = $builder->createVehicle($Chunk, $attributes);
$builder->putVehicle($vehicle);


$Chunk = $modx->newObject('modChunk');
$Chunk->fromArray(array(
    'name' => 'eway_success',
    'description' => 'Formatting Chunk for Eway success messages. <strong>DO NOT MODIFY</strong>. Please make a copy of this Chunk and reference it in your &successTpl parameter',
    'snippet' => file_get_contents('../core/components/eway/elements/chunks/eway_success.html'),
));

$Chunk->addOne($Category);

$attributes = array(
	xPDOTransport::UNIQUE_KEY => 'name',
	xPDOTransport::PRESERVE_KEYS => false,
	xPDOTransport::UPDATE_OBJECT => true,
    xPDOTransport::RELATED_OBJECTS => true,
    xPDOTransport::RELATED_OBJECT_ATTRIBUTES => array (
        'Category' => array(
            xPDOTransport::PRESERVE_KEYS => true,
            xPDOTransport::UPDATE_OBJECT => false,
            xPDOTransport::UNIQUE_KEY => array('category'),
        ),
    ),
	
);

$vehicle = $builder->createVehicle($Chunk, $attributes);
$builder->putVehicle($vehicle);



/* now pack in the license file, readme and setup options */
$builder->setPackageAttributes(array(
    'license' => file_get_contents(MODX_ASSETS_PATH .'components/'.PKG_NAME_LOWER.'/core/components/'.PKG_NAME_LOWER.'/docs/license.txt'),
    'readme' => file_get_contents(MODX_ASSETS_PATH .'components/'.PKG_NAME_LOWER.'/core/components/'.PKG_NAME_LOWER.'/docs/readme.txt'),
    'changelog' => file_get_contents(MODX_ASSETS_PATH .'components/'.PKG_NAME_LOWER.'/core/components/'.PKG_NAME_LOWER.'/docs/changelog.txt'),
//    'setup-options' => array(
//        'source' => MODX_ASSETS_PATH .'components/docs/user.input.html',
//   ),
));


// Zip up the package
$builder->pack();

echo '<br/>Package complete. Check your '.MODX_CORE_PATH . 'packages/ directory for the newly created package.';
/*EOF*/