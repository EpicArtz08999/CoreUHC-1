<?php

namespace CoreUHC\commands;

use CoreUHC\Main;

use pocketmine\command\CommandSender;

use CoreUHC\commands\CoreUHCCommandListener;

use pocketmine\utils\TextFormat as TF;

class CoreUHCTeamCommand extends CoreUHCCommandListener{
	
	private $plugin;
	
	public function __construct(Main $plugin){
		$this->plugin = $plugin;
		$this->server = $this->plugin->getServer();
		parent::__construct($plugin, "team", "CoreUHC Team command!", "/team", "t");
		$this->setPermission("coreuhc.command.team");
    }
	
	public function getPlugin(){
		return $this->plugin;
	}

	public function getServer(){
		return $this->server; 
	}
	
	public function execute(CommandSender $sender, $commandLabel, array $args){
		$count = $this->getPlugin()->teamCount;
		if(!$this->getPlugin()->teamsEnabled()){
			$sender->sendMessage(Main::PREFIX."Teams are not enabled!");
			return;
		}
		if(isset($args[0]) && strtolower($args[0]) === "tp"){
			if(isset($args[1])){
				$player = $this->getServer()->getPlayer($args[1]);
				if($player === null){
					$sender->sendMessage(Main::PREFIX."That player isn't online!");
					return;
				} 
				if($this->getPlugin()->getTeam($player)->getName() === $this->getPlugin()->getTeam($player)->getName()){
					$sender->teleport($player);
					$sender->sendMessage(Main::PREFIX."Teleporting to teammate ".$player->getName()."!");
					$player->sendMessage(Main::PREFIX.$sender->getName()." telported to you!");
				}else{
					$sender->sendMessage(Main::PREFIX.$player->getName()." isn't on your team!");
				}
			}
		}
		if(isset($args[0]) && strtolower($args[0]) === "disband"){
			if($this->getPlugin()->isInTeam($sender) && $this->getPlugin()->getTeam($sender)->getLeader()->getName() === $sender->getName()){
				$sender->sendMessage(Main::PREFIX."Team disbaned!");
				$this->getPlugin()->removeTeam($this->getPlugin()->getTeam($sender)->getName());
			}else{
				$sender->sendMessage(Main::PREFIX."You don't own a team!");
			}
		}
		if(isset($args[0]) && strtolower($args[0]) === "create"){
			if(isset($this->getPlugin()->playerTeam[$sender->getName()])){
				$sender->sendMessage(Main::PREFIX."You already are in a team/own a team!");
				return;
			}
			$this->getPlugin()->createTeam($sender, "Team".$count);
			$this->getPlugin()->teamCount++;
		}
		if(isset($args[0]) && strtolower($args[0]) === "invite"){
			if($this->getPlugin()->isInTeam($sender) && $this->getPlugin()->getTeam($sender)->getLeader()->getName() === $sender->getName()){
				if(count($this->getPlugin()->getTeam($sender)->getPlayerCount()) === $this->getPlugin()->teamLimit){
					$sender->sendMessage(Main::PREFIX."You have the max player limit!");	
					return;
				}
				if(isset($args[1])){
					$player = $this->getServer()->getPlayer($args[1]);
					if($player === null){
						$sender->sendMessage(Main::PREFIX."That player isn't online!");
						return;
					}
					$player->sendMessage(Main::PREFIX.$sender->getName()." sent you a team request please do /team accept to accept!");
					$sender->sendMessage(Main::PREFIX."Sent a team request to ".$sender->getName()."!");
					$this->getPlugin()->handleRequest($sender, $player);
				}else{
					$sender->sendMessage(Main::PREFIX."Please specify a player!");
				}
			}else{
				$sender->sendMessage(Main::PREFIX."Please join/create a team to use this command!");
			}
		}
		if(isset($args[0]) && strtolower($args[0]) === "accept"){
			if(isset($this->getPlugin()->waiting[$sender->getName()])){
				$requester = $this->getServer()->getPlayer($this->getPlugin()->requester[$sender->getName()]);
				$sender->sendMessage(Main::PREFIX."Accepted ".$requester->getName()."'s team request!");
				$this->getPlugin()->setTeam($sender, $this->getPlugin()->getTeam($requester)->getName());
				foreach($this->getPlugin()->getTeam($requester)->getTeammates() as $tm){
					$tm = $this->getServer()->getPlayer($tm);
					$tm->sendMessage(Main::PREFIX.$sender->getName()." has joined the team!");
				}
				$this->getPlugin()->getTeam($requester)->addPlayer($sender);
				$this->getPlugin()->closeRequest($sender);
			}else{
				$sender->sendMessage(Main::PREFIX."You don't have any team request!");
			}
		}
	}
}