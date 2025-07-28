<?php
ini_set('display_errors', 1);
function checkFileAndChannel($filename){
    //cas où la chaine n'est pas passer en paramètre
    if(!isset($_GET["channel"])){
        echo json_encode(["error"=>1,"errString"=>"no channel parameter"]);
        return false;
    }
    
    //Gerer le cas où le fichier n'est plus accessible
    if(!file_exists($filename)){
        echo json_encode(["error"=>0,"errString"=>`file '{$filename}' doesn't exist`]);
        return false;
    }
    return true;
}
    if(isset($_GET["act"])){
        switch ($_GET["act"]){
            case "addMessage":{
                $json_data = json_decode(file_get_contents('php://input'), true);//<=Variable POST
                $filename="tmp/live.json";
                if(!checkFileAndChannel($filename)){
                    return;
                }
                $file=file_get_contents($filename);
                $data=json_decode($file,true);
                $channel=$data[$_GET["channel"]];
                $messages=$channel["messages"];
                if (in_array($json_data["message"], $messages)) {
                    echo json_encode(["error"=>2,"errString"=>"message already setin"]);
                    return;
                }
                $messages[]=$json_data["message"];
                $data[$_GET["channel"]]["messages"]=$messages;
                $str=json_encode($data);
                if(file_put_contents($filename,$str)){
                   echo json_encode(["result"=>"OK"]);
                }else{
                    echo json_encode(["error"=>3,"errString"=>"message file cannot be rewriting"]);
                }
                break;
            }
            case "getMessages":{
                $filename="tmp/live.json";
                if(!checkFileAndChannel($filename)){
                    return;
                }
                $file=file_get_contents($filename);
                $data=json_decode($file,true)[$_GET["channel"]];
                echo json_encode(["result"=>"OK","data"=>$data["messages"]]);
                break;
            }
            case "fileWrite":{
                $json_data = json_decode(file_get_contents('php://input'), true);
                echo $json_data["file"];
                if(!file_exists($json_data["file"])){
                    echo json_encode(["error"=>0,"errString"=>`{$json_data["file"]} doesn't exist`]);
                    return;
                }
                $file=file_get_contents($json_data["file"]);
                if(isset($_GET["overwrite"]) && $_GET["overwrite"]=="1")
                    $file=$json_data["message"];
                else
                    $file.=$json_data["message"];
                $return = file_put_contents($json_data["file"],$file);
                if($return)
                    echo json_encode(["result"=>"OK"]);
                else
                    echo json_encode(["error"=>1,"errString"=>`cannot write in the file`]);
                break;
            }
            case "readFile":{
                $json_data = json_decode(file_get_contents('php://input'), true);
                if(!file_exists($json_data["file"])){
                    echo json_encode(["error"=>0,"errString"=>`{$json_data["file"]} doesn't exist`]);
                    return;
                }
                $file=file_get_contents($json_data["file"]);
                echo json_encode(["result"=>"OK","data"=>$file]);
                break;
            }
        }
    }