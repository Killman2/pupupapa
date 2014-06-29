<?php
 
/*
__PocketMine Plugin__
name=SignStats
description=
version=1.0.1
author=Killman2
class=SignStats
apiversion=11,12,13
*/
 
class SignStats implements Plugin{  
    private $api;
    
    public function __construct(ServerAPI $api, $server = false){
        $this->api = $api;
        $this->server = ServerAPI::request();
    }
    
    public function init(){
        $this->api->addHandler("tile.update", array($this, "eventHandler"), 5);
        $this->api->schedule(20, array($this, "timerUpdateSign"), array(), true);
    }
    
    public function eventHandler($data, $event)
    {
        switch($event)
        {
            case "tile.update":
            if(!($data instanceof Tile)){return;}
            if($data->class != TILE_SIGN){return;}
            if ($data->data["Text1"] == 'online'){
            $world = $this->api->level->getDefault();
                if ($world)
                {
                $players = count($world->players);
                $data->data["Text1"]="[SignStats]";
                $data->data["Text2"]= "Players Online:";
                $data->data["Text3"]= $players;
                $data->data["Text4"]= "============================";
                $this->api->tile->spawnToAll($data);
                }
                else
                {
                $data->data["Text1"]="ERROR";
                $data->data["Text2"]="ERROR";
                $this->api->tile->spawnToAll($data);
                }
            }
            break;
        }
    }
    
    public function updateSignText($tile, $target = false, $t1 = "", $t2 = "", $t3 = "", $t4 = ""){
        if(!($tile instanceof Tile)){return;}
        if($tile->class != TILE_SIGN){return;}
        $nbt = new NBT();
        $nbt->write(chr(NBT::TAG_COMPOUND)."\x00\x00");
        
        $nbt->write(chr(NBT::TAG_STRING));
        $nbt->writeTAG_String("Text1");
        $nbt->writeTAG_String($t1);
        
        $nbt->write(chr(NBT::TAG_STRING));
        $nbt->writeTAG_String("Text2");
        $nbt->writeTAG_String($t2);
            
        $nbt->write(chr(NBT::TAG_STRING));
        $nbt->writeTAG_String("Text3");
        $nbt->writeTAG_String($t3);
        
        $nbt->write(chr(NBT::TAG_STRING));
        $nbt->writeTAG_String("Text4");
        $nbt->writeTAG_String($t4);
        $nbt->write(chr(NBT::TAG_STRING));
        $nbt->writeTAG_String("id");
        $nbt->writeTAG_String($tile->class);
        $nbt->write(chr(NBT::TAG_INT));
        $nbt->writeTAG_String("x");
        $nbt->writeTAG_Int((int) $tile->x);
    
        $nbt->write(chr(NBT::TAG_INT));
        $nbt->writeTAG_String("y");
        $nbt->writeTAG_Int((int) $tile->y);
                
        $nbt->write(chr(NBT::TAG_INT));
        $nbt->writeTAG_String("z");
        $nbt->writeTAG_Int((int) $tile->z);
                
        $nbt->write(chr(NBT::TAG_END)); 
 
        $pk = new EntityDataPacket();
        $pk->x = $tile->x;
        $pk->y = $tile->y;
        $pk->z = $tile->z;
        $pk->namedtag = $nbt->binary;
        if($target instanceof Player){
            $target->dataPacket($pk);
        }else{
            $players = $this->api->player->getAll($tile->level);
            foreach($players as $pIndex => $player){
                if($player->spawned == false){unset($players[$pIndex]);}
            }
            $this->api->player->broadcastPacket($players, $pk);
        }
    }
    
    public function timerUpdateSign(){
        $tiles = array();
        $l = $this->server->query("SELECT ID FROM tiles WHERE class = '".TILE_SIGN."';");
        if($l !== false and $l !== true){
            while(($t = $l->fetchArray(SQLITE3_ASSOC)) !== false){
                $t = $this->api->tile->getByID($t["ID"]);
                if($t instanceof Tile){
                    $tiles[$t->id] = $t;
                }
            }
        }
        foreach($tiles as $tile){
            if($tile->data["Text1"] == "[SignStats]"){
            $lv = $this->api->level->getDefault();
            if (!$lv){continue;}
            $players = count($lv->players);
            $this->updateSignText($tile, false,$tile->data["Text1"],$tile->data["Text2"], $players, $tile->data["Text4"]);
            }
        }
    }
    
    public function __destruct(){   
    }
}
