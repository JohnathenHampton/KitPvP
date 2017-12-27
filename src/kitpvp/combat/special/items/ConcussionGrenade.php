<?php namespace kitpvp\combat\special\items;

use kitpvp\combat\special\items\types\Throwable;

use pocketmine\Player;
use pocketmine\entity\Effect;
use pocketmine\network\mcpe\protocol\LevelEventPacket;

use kitpvp\KitPvP;

class ConcussionGrenade extends Throwable{

	public function __construct($meta = 0, $count = 1){
		parent::__construct(384, $meta, "Concussion Grenade");
		$this->setCount($count);

		$this->init();
	}

	public function getDescription(){
		return "Blinds and slows down opponents within 5 blocks. Easy escape route.";
	}

	public function concuss(Player $player, Player $thrower){
		$combat = KitPvP::getInstance()->getCombat();
		$teams = $combat->getTeams();
		if($teams->sameTeam($player, $thrower)){
			return;
		}
		$combat->getSlay()->damageAs($thrower, $player, 5);

		$pk = new LevelEventPacket();
		$pk->evid = 3501;
		$pk->position = $player->asVector3();
		$pk->data = 0;
		foreach($player->getViewers() as $p) $p->dataPacket($pk);

		$player->addEffect(Effect::getEffect(Effect::SLOWNESS)->setDuration(20 * 8)->setAmplifier(3));
		$player->addEffect(Effect::getEffect(Effect::BLINDNESS)->setDuration(20 * 8));
	}

}