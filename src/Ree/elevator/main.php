<?php

namespace Ree\elevator;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\utils\Config;
use pocketmine\math\Vector3;

use pocketmine\event\player\PlayerJumpEvent;
use pocketmine\event\player\PlayerToggleSneakEvent;

class main extends PluginBase implements Listener
{
    const block = 42;
    const elevator = 148;
    /**
     * @var bool|mixed
     */
    private $authority;

    /**
     * @var string[]
     */
    private array $world;
    private string $message;

    /**
     * @var bool[]
     */
    private array $sneak;

    public function onEnable(): void
    {
        $this->getLogger()->info("loading now...");
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $elevator = new Config($this->getDataFolder() . "elevator.yml", Config::YAML, array(
            'Authority required for use//使用に必要な権限[op or true]' => 'op',
            'Available world//使用可能なワールド[string]' => 'world,lobby',
            'message//メッセージ[true or false]' => 'true'
        ));
        $this->authority = $elevator->get("Authority required for use//使用に必要な権限[op or true]");
        $this->world = $elevator->get("Available world//使用可能なワールド[string]");
        $this->world = explode(",", $this->world);
        $this->message = $elevator->get("message//メッセージ[true or false]");
    }

    private function elevator($level, $x, $y, $z): bool
    {
        if (self::elevator == $level->getBlock(new Vector3($x, $y, $z))->getId()) {
            if (self::block == $level->getBlock(new Vector3($x, $y - 1, $z))->getId()) {
                return true;
            }
        }
        return false;
    }

    public function onJump(PlayerJumpEvent $ev)
    {
        $p = $ev->getPlayer();
        $world = $p->getWorld();
        $x = $p->getPosition()->getX();
        $y = $p->getPosition()->getY();
        $z = $p->getPosition()->getZ();

        if ($this->request($p)) {
            foreach ($this->world as $worldName) {
                if ($world->getFolderName() == $worldName) {
                    if ($this->elevator($world, $x, $y, $z)) {
                        for ($i = 2; $i <= 30; $i++) {
                            if (self::elevator == $world->getBlock(new Vector3($x, $y + $i, $z))->getId()) {
                                if (self::block == $world->getBlock(new Vector3($x, $y + $i - 1, $z))->getId()) {
                                    $p->teleport(new Vector3($x, $y + $i, $z));
                                    if ($this->message == true) {
                                        $p->sendMessage("エレベーターを使用して" . $i . "ブロック上がりました");
                                    }
                                    break;
                                }
                            }
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
        $world = $p->getWorld();
        $x = $p->getPosition()->getX();
        $y = $p->getPosition()->getY();
        $z = $p->getPosition()->getZ();

        if (empty($this->sneak[$n])) {
            $this->sneak[$n] = true;
        }

        if ($ev->isSneaking()) {
            if ($this->request($p)) {
                if ($this->sneak[$n]) {
                    foreach ($this->world as $worldName) {
                        if ($world->getFolderName() == $worldName) {
                            if ($this->elevator($world, $x, $y, $z)) {
                                for ($i = 2; $i <= 30; $i++) {
                                    if (self::elevator == $world->getBlock(new Vector3($x, $y - $i, $z))->getId()) {
                                        if (self::block == $world->getBlock(new Vector3($x, $y - $i - 1, $z))->getId()) {
                                            $p->teleport(new Vector3($x, $y - $i, $z));
                                            if ($this->message == true) {
                                                $p->sendMessage("エレベーターを使用して" . $i . "ブロック下りました");
                                            }
                                            $this->sneak[$n] = false;
                                            break;
                                        }
                                    }
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

    private function request($p): bool
    {
        if ($this->authority) {
            return true;
        } else {
            if ($p->isOp()) {
                return true;
            } else {
                return false;
            }
        }
    }
}
