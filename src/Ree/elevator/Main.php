<?php

namespace Ree\elevator;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\Server;
use pocketmine\utils\Config;
use pocketmine\math\Vector3;

use pocketmine\event\player\PlayerJumpEvent;
use pocketmine\event\player\PlayerToggleSneakEvent;

class main extends PluginBase implements Listener
{
    const block = 42;
    const elevator = 148;
    
    public function onEnable ()
    {
       $this->getLogger()->info(">>loading now...");
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->elevator = new Config($this->getDataFolder() . "elevator.yml" ,Config::YAML ,array(
            'Authority required for use//使用に必要な権限[op or true]' => 'op',
            'Available world//使用可能なワールド[string]' => 'world,lobby'
            ));
        $this->world = $this->elevator->get("Available world//使用可能なワールド[string]");
        $this->world = explode("," , $this->world);
    }

    public function onJump (PlayerJumpEvent $ev)
    {
        $p = $ev->getPlayer();
        $n = $p->getName();
        $level = $p->getlevel();
        $x = $p->getX();
        $y = $p->getY();
        $z = $p->getZ();

        foreach($this->world as $world)
        {
            $world = $this->getServer()->getLevelByName($world);
            if($level == $world)
            {
                if($this->elevator($level ,$x ,$y ,$z))
                {
                     for($i = 2 ;$i <= 15 ;$i++)
                     {
                         if(self::elevator == $level->getBlock(new Vector3($x ,$y + $i ,$z))->getId())
                         {
                             if(self::block == $level->getBlock(new Vector3($x ,$y + $i - 1 ,$z))->getId())
                             {
                                 $p->teleport(new Vector3($x ,$y + $i ,$z));
                                 $p->sendMessage("エレベーターを使用して".$i."ブロック上がりました");
                                 break;
                             }
                         }
                     }
                }
            }
        }
    }

    public function onSneak (PlayerToggleSneakEvent $ev)
    {
        $p = $ev->getPlayer();
        $n = $p->getName();
        $level = $p->getlevel();
        $x = $p->getX();
        $y = $p->getY();
        $z = $p->getZ();

        foreach($this->world as $world)
        {
            $world = $this->getServer()->getLevelByName($world);
            if($level == $world)
            {
                if($this->elevator($level ,$x ,$y ,$z))
                {
                    for ($i = 2; $i <= 15; $i++)
                    {
                        if (self::elevator == $level->getBlock(new Vector3($x, $y - $i, $z))->getId())
                        {
                            if (self::block == $level->getBlock(new Vector3($x, $y - $i - 1, $z))->getId())
                            {
                                $p->teleport(new Vector3($x, $y - $i, $z));
                                $p->sendMessage("エレベーターを使用して" . $i . "ブロック下りました");
                                break;
                            }
                        }
                    }
                }
            }
        }
    }

    private function elevator ($level ,$x ,$y ,$z)
    {
        $vector3 = new Vector3($x ,$y ,$z);

        if(self::elevator == $level->getBlock(new Vector3($x ,$y ,$z))->getId())
        {
            if(self::block == $level->getBlock(new Vector3($x ,$y - 1 ,$z))->getId())
            {
                return true;
            }
        }

        return false;
    }
}
