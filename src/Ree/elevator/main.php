<?php

namespace Ree\elevator;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\Server;
use pocketmine\utils\Config;
use pocketmine\math\Vector3;

use pocketmine\event\player\PlayerJumpEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerToggleSneakEvent;

class main extends PluginBase implements Listener
{
    public static $elevatorBlock;
    public static $elevatorPlate;
    public static $distanceMaximum;

    public function onEnable()
    {
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->elevator = new Config($this->getDataFolder() . "elevator.yml", Config::YAML, array(
            'permToUse' => 'true',
			'-Permission can be-' => 'op or true',
            'world' => 'world,lobby,*',
			'-World can be-' => '* for all world, or world separated by comma',
            'showMessage' => 'true',
            'elevatorBlock' => '42',
            'elevatorPlate' => '148',
			'distanceMaximum' => '15'
        ));
        $this->authority = $this->elevator->get("permToUse");
        $this->world = $this->elevator->get("world");
        $this->world = explode(",", $this->world);
		self::$elevatorBlock = $this->elevator->get("elevatorBlock");
		self::$elevatorPlate = $this->elevator->get("elevatorPlate");
		self::$distanceMaximum = $this->elevator->get("distanceMaximum");
        $this->message = $this->elevator->get("showMessage");
    }

	//Am i in an elevator ?
	public function onMove(PlayerMoveEvent $ev) {
		if (!$ev->getFrom()->equals($ev->getTo())) {
			//player has moved.

			$p = $ev->getPlayer();
			$n = $p->getName();
			$level = $p->getlevel();
			$x = $p->getX();
			$y = $p->getY();
			$z = $p->getZ();
			$s = 0;
					
			//Can i use it ? I'm in an enabled world ?
			if ($this->enabled($p)) {
				//Am i in an elevator ?
				if ($this->elevator($level, $x, $y, $z)) {
					//Try to check up
					for ($i = 2; $i <= self::$distanceMaximum; $i++) {
						if (self::$elevatorPlate == $level->getBlock(new Vector3($x, $y + $i, $z))->getId()) {
							if (self::$elevatorBlock == $level->getBlock(new Vector3($x, $y + $i - 1, $z))->getId()) {
								$s = $s+1;
								break;
							}
						}
					}
					//Try to check down
					for ($i = 2; $i <= self::$distanceMaximum; $i++) {
						if (self::$elevatorPlate == $level->getBlock(new Vector3($x, $y - $i, $z))->getId()) {
							if (self::$elevatorBlock == $level->getBlock(new Vector3($x, $y - $i - 1, $z))->getId()) {
								$s = $s+2;
								break;
							}
						}
					}
					if ($s == 1)
						$p->sendPopup("§aElevator: §6UP");						
					else if ($s == 2)
						$p->sendPopup("§aElevator: §6DOWN");		
					else if ($s == 3)
						$p->sendPopup("§aElevator: §6UP & DOWN");		
				}
			}
		}
	}

    private function elevator($level, $x, $y, $z)
    {
        $vector3 = new Vector3($x, $y, $z);

        if (self::$elevatorPlate == $level->getBlock(new Vector3($x, $y, $z))->getId()) {
            if (self::$elevatorBlock == $level->getBlock(new Vector3($x, $y - 1, $z))->getId()) {
                return true;
            }
        }

        return false;
    }

    public function onJump(PlayerJumpEvent $ev)
    {
        $p = $ev->getPlayer();
        $n = $p->getName();
        $level = $p->getlevel();
        $x = $p->getX();
        $y = $p->getY();
        $z = $p->getZ();
                
        if ($this->enabled($p)) {
            if ($this->elevator($level, $x, $y, $z)) {
                for ($i = 2; $i <= self::$distanceMaximum; $i++) {
                    if (self::$elevatorPlate == $level->getBlock(new Vector3($x, $y + $i, $z))->getId()) {
                        if (self::$elevatorBlock == $level->getBlock(new Vector3($x, $y + $i - 1, $z))->getId()) {
                            $p->teleport(new Vector3($x, $y + $i, $z));
                            if ($this->message == "true") {
                                $p->sendMessage("You go up for " . $i . " blocks");
                            }
                            break;
                        }
                    }
                }
            }
        }
    }

    public function onSneak(PlayerToggleSneakEvent $ev)
    {
        $p = $ev->getPlayer();
        $n = $p->getName();
        $level = $p->getlevel();
        $x = $p->getX();
        $y = $p->getY();
        $z = $p->getZ();

        if(empty($this->sneak[$n]))
        {
            $this->sneak[$n] = true;
        }

        if ($ev->isSneaking()) {					
			if ($this->enabled($p)) {
				if ($this->sneak[$n]) {
							if ($this->elevator($level, $x, $y, $z)) {
								for ($i = 2; $i <= self::$distanceMaximum; $i++) {
									if (self::$elevatorPlate == $level->getBlock(new Vector3($x, $y - $i, $z))->getId()) {
										if (self::$elevatorBlock == $level->getBlock(new Vector3($x, $y - $i - 1, $z))->getId()) {
											$p->teleport(new Vector3($x, $y - $i, $z));
											if ($this->message == "true") {
												$p->sendMessage("You go down for " . $i . " blocks");
											}
											$this->sneak[$n] = false;
											break;
										}
									}
								}
							}
				}
			}
        } else {
            $this->sneak[$n] = true;
        }
    }

    private function request ($p)
    {
        if($this->authority)
        {
            return true;
        }else{
            if($p->isOp())
            {
                return true;
            }else{
                return false;
            }
        }
    }
	
    private function enabled ($p)
    {
        if($this->request($p)) {
			//Enabled in all level ?
			if ($this->world[0] == "*") {
				return true;
			}
			else {
				//Check if we are in an enabled level
				foreach ($this->world as $world) {
					$world = $this->getServer()->getLevelByName($world);
					if ($level == $world ) return true;
				}
				return false;
			}
		}
		else return false;
    }
}