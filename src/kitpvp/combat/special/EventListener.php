<?php namespace kitpvp\combat\special;

use pocketmine\event\Listener;
use pocketmine\event\player\{
	PlayerInteractEvent
};
use pocketmine\event\entity\{
	EntityDamageEvent,
	EntityDamageByEntityEvent,
	EntityDamageByChildEntityEvent
};
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\utils\TextFormat;
use pocketmine\network\mcpe\protocol\{
	PlayerActionPacket
};

use pocketmine\level\sound\{
	AnvilFallSound
};
use pocketmine\entity\{
	Entity,
	Effect
};
use pocketmine\item\Item;
use pocketmine\math\Vector3;

use pocketmine\nbt\tag\{
	CompoundTag,
	ListTag,
	FloatTag,
	DoubleTag,
	ShortTag
};

use kitpvp\KitPvP;
use kitpvp\combat\special\items\{
	SpecialWeapon,

	BookOfSpells,
	ConcussionGrenade,
	BrassKnuckles,
	Gun,
	ReflexHammer,
	Defibrillator,
	Syringe,
	ThrowingKnife,
	Shuriken,
	EnderPearl,
	Decoy,
	Flamethrower,
	MaloneSword
};
use kitpvp\combat\special\entities\{
	ThrownConcussionGrenade,
	Bullet,
	ThrownEnderpearl,
	ThrownDecoy
};

use core\AtPlayer as Player;

class EventListener implements Listener{

	public $plugin;
	public $special;

	public $action = [];

	public function __construct(KitPvP $plugin, Special $special){
		$this->plugin = $plugin;
		$this->special = $special;
	}

	public function onInteract(PlayerInteractEvent $e){
		$player = $e->getPlayer();
		$teams = $this->plugin->getCombat()->getTeams();
		$item = $e->getItem();
		if((!$this->plugin->getArena()->inArena($player)) || $this->plugin->getCombat()->getSlay()->isInvincible($player)){
			$e->setCancelled(true);
			return;
		}
		if($item instanceof BookOfSpells){
			if(!isset($this->special->special[$player->getName()]["book_of_spells"]) || ($this->special->special[$player->getName()]["book_of_spells"] + 15) - time() <= 0){
				$count = 0;
				$spells = $this->special->getSpells();
				$spell = $spells[mt_rand(0,count($spells) - 1)];
				foreach($player->getLevel()->getPlayers() as $p){
					if($p->distance($player) <= 10 && $p != $player){
						if($teams->inTeam($player) && $teams->inTeam($p)){
							if($teams->getPlayerTeamUid($player) != $teams->getPlayerTeamUid($p)){
								$spell->cast($player, $p);
								$count++;
							}
						}else{
							$spell->cast($player, $p);
							$count++;
						}
					}
				}
				if($count > 0) $this->special->special[$player->getName()]["book_of_spells"] = time();
			}
			return;
		}
		if($item instanceof ConcussionGrenade){
			if($e->getAction() == 3){
				if(!isset($this->special->special[$player->getName()]["concussion_grenade"]) || ($this->special->special[$player->getName()]["concussion_grenade"] + 5) - time() <= 0){
					$nbt = $this->createNbt($player);
					$force = 0.4;
					$cg = Entity::createEntity("ThrownConcussionGrenade", $player->getLevel(), $nbt, $player);
					$cg->setMotion($cg->getMotion()->multiply($force));
					$cg->spawnToAll();
					$new = clone $item;
					$new->setCount($new->getCount() - 1);
					$player->getInventory()->setItemInHand($new);
					$this->special->special[$player->getName()]["concussion_grenade"] = time();
				}
			}
		}
		if($item instanceof EnderPearl){
			if($e->getAction() == 3){
				if(!isset($this->special->special[$player->getName()]["enderpearl"]) || ($this->special->special[$player->getName()]["enderpearl"] + 1) - time() <= 0){
					$nbt = $this->createNbt($player);
					$force = 1.6;
					$enderpearl= Entity::createEntity("ThrownEnderpearl", $player->getLevel(), $nbt, $player);
					$enderpearl->setMotion($enderpearl->getMotion()->multiply($force));
					$enderpearl->spawnToAll();
					$new = clone $item;
					$new->setCount($new->getCount() - 1);
					$player->getInventory()->setItemInHand($new);
					$this->special->special[$player->getName()]["enderpearl"] = time();
				}
			}
		}

		if($item instanceof Decoy){
			if($e->getAction() == 3){
				if(!isset($this->special->special[$player->getName()]["decoy"]) || ($this->special->special[$player->getName()]["decoy"] + 1) - time() <= 0){
					$nbt = $this->createNbt($player);
					$force = 1.6;
					$decoy = Entity::createEntity("ThrownDecoy", $player->getLevel(), $nbt, $player);
					$decoy->setMotion($decoy->getMotion()->multiply($force));
					$decoy->spawnToAll();
					$new = clone $item;
					$new->setCount($new->getCount() - 1);
					$player->getInventory()->setItemInHand($new);
				}
			}
		}
		if($item instanceof Gun){
			if($e->getAction() == 3){
				if((!isset($this->special->special[$player->getName()]["gun"])) || ($this->special->special[$player->getName()]["gun"] + 3) - time() <= 0){
					$nbt = $this->createNbt($player);
					$force = 2.75;
					$bullet = Entity::createEntity("Bullet", $player->getLevel(), $nbt, $player);
					$bullet->setMotion($bullet->getMotion()->multiply($force));
					$bullet->spawnToAll();
					$this->special->special[$player->getName()]["gun"] = time();
				}
			}
			return;
		}
		if($item instanceof Flamethrower){
			if($e->getAction() == 3){
				if((!isset($this->special->special[$player->getName()]["flamethrower"])) || ($this->special->special[$player->getName()]["flamethrower"] + 3) - time() <= 0){
					$nbt = $this->createNbt($player);
					$flame = Entity::createEntity("Flame", $player->getLevel(), $nbt, $player);
					$flame->spawnToAll();
					$this->special->special[$player->getName()]["flamethrower"] = time();
				}
			}
			return;
		}
	}

	public function onDmg(EntityDamageEvent $e){
		$player = $e->getEntity();
		$teams = $this->plugin->getCombat()->getTeams();
		if($player instanceof Player){
			if((!$this->plugin->getArena()->inArena($player)) || $this->plugin->getCombat()->getSlay()->isInvincible($player)){
				$e->setCancelled(true);
				return;
			}
			if($e instanceof EntityDamageByEntityEvent){
				$killer = $e->getDamager();
				if($killer instanceof Player){
					$item = $killer->getInventory()->getItemInHand();
					if($teams->inTeam($player) && $teams->inTeam($killer)){
						if($teams->getPlayerTeamUid($player) == $teams->getPlayerTeamUid($killer)){
							$e->setCancelled(true);
							return;
						}
					}
					if((!$this->plugin->getArena()->inArena($killer)) || $this->plugin->getCombat()->getSlay()->isInvincible($killer)){
						$e->setCancelled(true);
						return;
					}
					//FIX BOOK OF SPELLS B
					/*if($item instanceof BookOfSpells){
						if(!isset($this->special->special[$killer->getName()]["book_of_spells"]) || ($this->special->special[$killer->getName()]["book_of_spells"] + 10) - time() <= 0){
							$spells = $this->special->getSpells();
							$spell = $spells[mt_rand(0,count($spells) - 1)];
							$spell->cast($killer, $player);
							$this->special->special[$killer->getName()]["book_of_spells"] = time();
						}
					}*/

					if($item instanceof BrassKnuckles){
						$e->setKnockback(0.65);
						$e->setDamage(mt_rand(0,2));
						$player->getLevel()->addSound(new AnvilFallSound($player));
					}

					if($item instanceof ReflexHammer){
						$e->setKnockback(0.55);
						$e->setDamage(1,3);
					}

					if($item instanceof Defibrillator){
						if(!isset($this->special->special[$player->getName()]["defibrillator"]) || ($this->special->special[$player->getName()]["defibrillator"] + 20) - time() <= 0){
							$this->plugin->getCombat()->getSlay()->strikeLightning($player);
							$player->addTitle(TextFormat::OBFUSCATED."KK".TextFormat::RESET.TextFormat::AQUA." CLEAR! ".TextFormat::OBFUSCATED."KK", TextFormat::YELLOW."ZAPPED!", 5, 20, 5);
							$killer->addTitle(TextFormat::OBFUSCATED."KK".TextFormat::RESET.TextFormat::AQUA." CLEAR! ".TextFormat::OBFUSCATED."KK", TextFormat::YELLOW."ZAPPED!", 5, 20, 5);
							$e->setDamage(3);
							$player->addEffect(Effect::getEffect(Effect::SLOWNESS)->setDuration(20 * 10)->setAmplifier(1));
							$player->addEffect(Effect::getEffect(Effect::NAUSEA)->setDuration(20 * 10)->setAmplifier(5));
							$this->special->special[$player->getName()]["defibrillator"] = time();
							$killer->sendTip(TextFormat::RED."Defibrillator available in 20 seconds.");
						}
					}

					if($item instanceof Syringe){
						$e->setDamage(5);
						$player->addEffect(Effect::getEffect(Effect::NAUSEA)->setDuration(20 * 15));
						$player->addEffect(Effect::getEffect(Effect::POISON)->setAmplifier(1)->setDuration(20 * 5));
						$killer->getInventory()->setItemInHand(Item::get(0));
					}

					if($item instanceof MaloneSword){
						$e->setKnockback(0.25);
						$e->setDamage(mt_rand(1,3));
						$e->setDamage(mt_rand(1,3), 4);
						$fire_chance = mt_rand(0,100);
						if($fire_chance <= 20){
							$player->setOnFire(2);
						}
						$wither_chance = mt_rand(0,100);
						if($wither_chance <= 5){
							$player->addEffect(Effect::getEffect(Effect::WITHER)->setAmplifier(1)->setDuration(20 * 3));
						}
					}

					if($e instanceof EntityDamageByChildEntityEvent){
						$child = $e->getChild();
						if($child instanceof ThrownConcussionGrenade){
							foreach($child->getLevel()->getPlayers() as $player){
								if($player->distance($child) <= 5 && $player != $killer){
									$this->special->cg($player, $killer);
								}
							}
						}
						if($child instanceof Bullet){
							$e->setDamage(3);
						}
						if($child instanceof ThrownEnderPearl){
							$killer->teleport($player);
						}
						if($child instanceof ThrownDecoy){
							$this->plugin->getKits()->setInvisible($killer, true);
							$this->special->special[$killer->getName()]["decoy"] = time();
						}
					}
				}
			}
		}
	}

	public function createNbt(Player $player){
		$aimPos = new Vector3(
			-sin($player->yaw / 180 * M_PI) * cos($player->pitch / 180 * M_PI),
			-sin($player->pitch / 180 * M_PI),
			cos($player->yaw / 180 * M_PI) * cos($player->pitch / 180 * M_PI)
		);
		$nbt = new CompoundTag("", [
			new ListTag("Pos", [
				new DoubleTag("", $player->x),
				new DoubleTag("", $player->y + $player->getEyeHeight()),
				new DoubleTag("", $player->z)
			]),
			new ListTag("Motion", [
				new DoubleTag("", $aimPos->x),
				new DoubleTag("", $aimPos->y),
				new DoubleTag("", $aimPos->z)
			]),
			new ListTag("Rotation", [
				new FloatTag("", $player->yaw),
				new FloatTag("", $player->pitch)
			]),
		]);
		return $nbt;
	}

}