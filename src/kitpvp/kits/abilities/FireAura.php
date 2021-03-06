<?php namespace kitpvp\kits\abilities;

use pocketmine\Player;
use pocketmine\level\particle\FlameParticle;
use pocketmine\entity\Living;

use kitpvp\KitPvP;

class FireAura extends Ability{

	public function __construct(){
		parent::__construct(
			"fire aura",
			"Automatically attack nearby enemies",
			true, -1, 100, false, true
		);
	}

	public function tick(){
		$player = $this->player;

		if(KitPvP::getInstance()->getArena()->inSpawn($player)) return true;

		$dmg = false;
		$teams = KitPvP::getInstance()->getCombat()->getTeams();
		$spec = KitPvP::getInstance()->getArena()->getSpectate();
		foreach($player->getLevel()->getNearbyEntities($player->getBoundingBox()->expandedCopy(5, 5, 5)) as $p){
			if($p != $player && $p instanceof Living && (!$p instanceof Player || (!$teams->sameTeam($player, $p) && !$spec->isSpectating($p)))){
				if($p->getHealth() - 2 <= 0){}else{
					$dmg = true;
					KitPvP::getInstance()->getCombat()->getSlay()->damageAs($player, $p, 2);
					for($i = 0; $i <= 5; $i++){
						$p->getLevel()->addParticle(new FlameParticle($p->add((mt_rand(-10,10)/10),(mt_rand(0,20)/10),(mt_rand(-10,10)/10))));
					}
				}
			}
		}
		if($dmg){
			for($i = 0; $i <= 5; $i++){
				$player->getLevel()->addParticle(new FlameParticle($player->add((mt_rand(-10,10)/10),(mt_rand(0,20)/10),(mt_rand(-10,10)/10))));
			}
		}
		return true;
	}

}