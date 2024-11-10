<?php

function getDominantColors($fileDir, $numberOfColors = 5)
{
    $image = null;
    $type = explode(".", $fileDir);
    $type = $type[count($type) - 1];
    switch($type)
    {
        case "jpg":
        case "jpeg":
            $image =  imagecreatefromjpeg($fileDir);
            break;
        case "png":
            $image =  imagecreatefrompng($fileDir);
            break;
    }

    $width = imagesx($image);
    $height = imagesy($image);
    $imageSize = $width * $height;
    $colors = [];
    
    for ($y = 0; $y < $height; $y++) {
        for ($x = 0; $x < $width; $x++) {
            $rgb = imagecolorat($image, $x, $y);
            $r = ($rgb >> 16) & 0xFF;
            $g = ($rgb >> 8) & 0xFF;
            $b = $rgb & 0xFF;
            
            $rgbStr = "$r,$g,$b";
            if(!isset($colors[$rgbStr])) $colors[$rgbStr] = 0;
            $colors[$rgbStr]++;
        } 
    }

    arsort($colors);

    $dominantColors = [];
    
    foreach (array_slice($colors, 0, $numberOfColors) as $clr => $count) {
        $dominantColors[$clr] = number_format($count / $imageSize * 100, 2);
    }
 
    return $dominantColors;
}

function handleImageUpload()
{
    $root = "./uploads/";
    $fileName = $_POST["filename"];
    $chunk = $_FILES["chunk"];
    $currChunkNumber = $_POST["currChunkNumber"];
    $numberOfChunks = $_POST["numberOfChunks"];
    $targetFolder = $root.getFileNameWithoutExt($fileName)."/";
    $targetChunk = $targetFolder."/".$currChunkNumber;
    $targetFile = $targetFolder.$fileName;
    $check = $_POST["check"];

    if(!$check) throw new Error('noCheckSum');

    try 
    {
        if(!is_dir($root)) mkdir($root);
        
        if(is_file($targetFile))
        {
            deleteFolder($targetFolder);
        }
        if(!is_dir($targetFolder)) mkdir($targetFolder);
        
        move_uploaded_file($chunk["tmp_name"], $targetChunk);
        
        if($currChunkNumber >= $numberOfChunks) // if the file has finished uploading
        {
            for ($i=0; $i < $currChunkNumber; $i++) 
            { 
                $currTargetChunk = $targetFolder.$i+1;
                $file = fopen($currTargetChunk, "rb");
                $fileChunk = fread($file, filesize($currTargetChunk));
                
                if(!doCheckSum($currTargetChunk, $check)) 
                {
                    deleteFolder($targetFolder);
                    throw new Error('checkSumFailed');
                }

                fclose($file);

                $final = fopen($targetFile, "ab");
                fwrite($final, $fileChunk);
                fclose($final);
                unlink($currTargetChunk);
            }
            return $targetFile;
        }

    }
    catch (Exception $err) 
    {
        rmdir($targetFolder); // if chunk upload failed, remove the file's folder to save up space on the server and avoid future data conflict

        throw new Error($err);
    }
    
}

function deleteFolder($targetFolder)
{
    array_map('unlink', glob("$targetFolder/*"));
    rmdir($targetFolder);
}

function getFileNameWithoutExt($fileName)
{
    $res = explode(".", $fileName);
    array_pop($res);
    $res = implode($res);
    return $res;
}

function doCheckSum($targetChunk, $check)
{
    $localCheck = 0;
    $chunk = file_get_contents($targetChunk);
    $chunk = bin2hex($chunk);

    $length = strlen($chunk);
    for ($i = 0; $i < $length; $i++) {
        $localCheck += ord($chunk[$i]);
    }
    return (int)$check === (int)$localCheck;
}


switch ($_GET["action"]) {
    case 'getImageColors':
        try {
            $file = handleImageUpload();
            if($file) 
            {
                $dominantColors = getDominantColors($file);
                die( json_encode($dominantColors) );
            }
        } catch (Error $th) {
            $response = ['status' => 'error', 'message' => $th->getMessage()];

            die( json_encode($response) );
        }
        break;
    default:
        throw new Exception("No server action specified!", 403);
        break;
}
