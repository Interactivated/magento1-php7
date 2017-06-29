<?php
/**
 * Created by PhpStorm.
 * User: Dmytro Portenko
 * Date: 6/26/17
 * Time: 4:06 PM
 */
require 'app/Mage.php';

//date_default_timezone_set('Africa/Lagos');

$magentoVersion = Mage::getVersion();
$inchooPatchLink = 'https://github.com/Inchoo/Inchoo_PHP7/archive/2.1.1.zip';
if (version_compare($magentoVersion, '1.9.3', '<')){
    $inchooPatchLink = 'https://github.com/Inchoo/Inchoo_PHP7/archive/1.0.7.zip';
}

$zipFile = dirname(__FILE__) . "/inchoo_php7.zip";
$zipResource = fopen($zipFile, "w");

/* Get The Zip File From Server */
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $inchooPatchLink);
curl_setopt($ch, CURLOPT_FAILONERROR, true);
curl_setopt($ch, CURLOPT_HEADER, 0);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_AUTOREFERER, true);
curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
curl_setopt($ch, CURLOPT_FILE, $zipResource);
$page = curl_exec($ch);
curl_close($ch);

/* Open the Zip file */
$zip = new ZipArchive;
$extractPath = dirname(__FILE__) . "/inchoo_php7";
$zip->open($zipFile);
/* Extract Zip File */
$zip->extractTo($extractPath);
$zip->close();

$subdirs = glob($extractPath . '/*' , GLOB_ONLYDIR);

//`chmod -R 755 app/code/local`;
chmod('app/code/local', 0755);

if(isset($subdirs[0])) {
    recurse_copy($subdirs[0] . '/app', dirname(__FILE__) . '/app');
}

/* remove zip file */
unlink($zipFile);
deleteDir($extractPath);

`patch lib/Varien/File/Uploader.php < php7_uploader`;

function deleteDir($path) {
    return is_file($path) ?
        @unlink($path) :
        array_map(__FUNCTION__, glob($path.'/*')) == @rmdir($path);
}

function recurse_copy( $src, $dst ) {

    $dir = opendir( $src );
    @mkdir(  $dst  );

    while( false !== ( $file = readdir( $dir ) ) ) {
        if( $file != '.' && $file != '..' ) {
            if( is_dir( $src . DS . $file ) ) {
                recurse_copy( $src . DS . $file, $dst . DS . $file );
            } else {
                copy( $src . DS . $file, $dst . DS . $file );
            }
        }
    }
    closedir( $dir );
}
