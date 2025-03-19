<?php
namespace Syphon\Spin;

use pocketmine\plugin\PluginBase;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;

use pocketmine\player\Player;

use pocketmine\entity\object\ItemEntity;
use pocketmine\entity\Location;

use pocketmine\item\VanillaItems;
use pocketmine\item\StringToItemParser;

use pocketmine\math\Vector3;

use pocketmine\utils\Config;
class Main extends PluginBase {
    private $spinningPlayers = []; // Track who's spinning
    private $cfg;

    public function onEnable(): void {
        $this->saveDefaultConfig();
        $this->cfg = new Config($this->getDataFolder() . "config.yml", Config::YAML);
    }

    public function onCommand(CommandSender $sender, Command $cmd, string $label, array $args): bool {
        if ($cmd->getName() === "spin") {
            if (isset($args[0]) && $args[0] === "reload") {
                if (!$sender->hasPermission("spin.manage")) {
                    $sender->sendMessage("No perms, bro! Can’t reload.");
                    return true;
                }
                
                $this->cfg = $this->cfg = new Config($this->getDataFolder() . "config.yml", Config::YAML);
                $sender->sendMessage("Config reloaded!");
                return true;
            }
            if (isset($this->spinningPlayers[$sender->getName()])) {
                $sender->sendMessage("Chill, fam! One spin at a time, wait it out!");
                return true;
            }
            if ($sender instanceof Player) {
                $this->startSpin($sender);
            }
            return true;
        }
        return false;
    }

    public function startSpin(Player $p) {
        $this->spinningPlayers[$p->getName()] = true; // Mark player as spinning
        
        $pos = $p->getPosition();
        $rad = $this->cfg->get("radius");
        $itemsYo = [];
        $angle = 0.0;
        $spd = $this->cfg->get("spin-speed");

        $itemList = [];
        $stuff = $this->cfg->get("items");
        foreach ($stuff as $name => $deets) {
            $count = $deets["count"];
            $item = StringToItemParser::getInstance()->parse($name);
            if ($item === null) {
                $this->getLogger()->warning("Yo, wtf is this item: " . $name);
                continue;
            }
            for ($i = 0; $i < $count; $i++) {
                $itemList[] = clone $item;
            }
        }
        
        shuffle($itemList);

        foreach ($itemList as $idx => $item) {
            $itemAngle = 2 * M_PI * $idx / count($itemList);
            $x = $pos->getX() + $rad * cos($itemAngle);
            $z = $pos->getZ() + $rad * sin($itemAngle);
            $y = $pos->getY() + 1;
            $spot = new Location($x, $y, $z, $p->getWorld(), 0, 0);
            $thing = new ItemEntity($spot, $item);
            $thing->setPickupDelay(32767);
            $thing->setNameTagVisible(true);
            $thing->setNameTagAlwaysVisible(true);
            $thing->setNameTag($item->getName());
            $thing->spawnToAll();
            $itemsYo[] = $thing;
        }

        $topX = $pos->getX() + $rad * cos(0);
        $topZ = $pos->getZ() + $rad * sin(0); // Fixed the syntax error here
        $topY = $pos->getY() + 2;
        $topSpot = new Location($topX, $topY, $topZ, $p->getWorld(), 0, 0);
        $stick = VanillaItems::STICK();
        $topThing = new ItemEntity($topSpot, $stick);
        $topThing->setPickupDelay(32767);
        $topThing->setHasGravity(false);
        $topThing->setNameTagVisible(true);
        $topThing->setNameTagAlwaysVisible(true);
        $topThing->setNameTag($this->cfg->get("stick-nametag"));
        $topThing->setMotion(new Vector3(0, 0, 0));
        $topThing->spawnToAll();

        $this->getScheduler()->scheduleRepeatingTask(new SpinTask($this, $itemsYo, $topThing, $pos, $angle, $spd, $p, $rad), 1);
    }

    public function pickItem(array $itemsYo, ItemEntity $topThing, Player $p, Vector3 $pos, float $rad): void {
        $winner = null;
        $minDist = PHP_INT_MAX;

        foreach ($itemsYo as $itm) {
            if ($itm->isClosed() || $itm->isFlaggedForDespawn()) {
                continue;
            }
            $dist = $itm->getPosition()->distanceSquared($topThing->getPosition());
            if ($dist < $minDist) {
                $minDist = $dist;
                $winner = $itm;
            }
        }

        foreach ($itemsYo as $itm) {
            if ($itm !== $winner && !$itm->isClosed() && !$itm->isFlaggedForDespawn()) {
                $itm->flagForDespawn();
            }
        }

        if ($winner !== null && $p->isOnline() && !$topThing->isClosed()) {
            $topX = $pos->getX() + $rad * cos(0);
            $topY = $pos->getY() + 2;
            $topZ = $pos->getZ() + $rad * sin(0);
            $upSpot = new Vector3($topX, $topY, $topZ);
            $startSpot = $winner->getPosition();
            $downSpot = new Vector3($topX, $pos->getY() + 0.5, $topZ);
            $stepsUp = $this->cfg->get("steps-up");
            $stepsDown = $this->cfg->get("steps-down");

            $this->getScheduler()->scheduleRepeatingTask(new SelectItemTask($this, $winner, $topThing, $startSpot, $upSpot, $downSpot, $stepsUp, $stepsDown, $p), 1);
        }

        // Spin’s done, yeet the player from the list
        unset($this->spinningPlayers[$p->getName()]);
    }
}