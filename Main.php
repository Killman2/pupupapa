<?php
 
namespace OnlineSign;
 
use pocketmine\event\Listener;
 
use pocketmine\level\Position;
use pocketmine\tile\Tile;
use pocketmine\plugin\PluginBase;
 
use pocketmine\utils\Config;
 
use pocketmine\scheduler\CallbackTask;
 
class OnlineSign extends PluginBase implements Listener{
 
 private $config;
 
 public function onEnable(){
  @mkdir($this->getDataFolder());
 
 $this->config = (new Config($this->getDataFolder()."config.yml", Config::YAML, array(
 "x" => 138,
 "y" => 67,
 "z" => 120,
 "level" => 'world',
 )))->getAll();
 $this->getServer()->getPluginManager()->registerEvents($this, $this);
 $task = new CallbackTask(array($this, "repeatedFunction"), array("item 2"));
 $this->getServer()->getScheduler()->scheduleRepeatingTask($task, 20);
   $this->getLogger()->info(TextFormat::RED . "[SignStats]" TextFormat::AQUA . "I'm sucsessfully loaded!");
 }
 
 public function onEvent(EntityLevelChangeEvent $event){
 
 }
 
 public function repeatedFunction(){
 $tile = $this->getServer()->getLevel($this->config['level'])->getTile(new Position($this->config['x'], $this->config['y'], $this->config['z']));
 if($tile instanceof Tile){
$tile->setText('[SignStats]', 'PLAYERS: '.count($this->getServer()->getOnlinePlayers()).'/'.$this->getServer()->getMaxPlayers(), 'TIME: '.date("H:i:s"), '------------------------------------');
 }
 }
 
 public function onDisable(){
 $config = (new Config($this->getDataFolder()."config.yml", Config::YAML));
 $config->setAll($this->config);
 $config->save();
   $this->getLogger()->info(TextFormat::RED . "[SignStats]" TextFromat::YELLOW . "I am sucsessfully unloaded! Hope you load me again :D");
 }
}
