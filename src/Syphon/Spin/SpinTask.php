<?php
namespace Syphon\Spin;

use pocketmine\scheduler\Task;
use pocketmine\entity\object\ItemEntity;
use pocketmine\math\Vector3;
use pocketmine\player\Player;

class SpinTask extends Task {
    private $plugin;
    private $itms;
    private $topEntity;
    private $ctr;
    private $ang;
    private $spd;
    private $p;
    private $rad;

    public function __construct(Main $plugin, array $itms, ItemEntity $topEntity, Vector3 $ctr, float $ang, float $spd, Player $p, float $rad) {
        $this->plugin = $plugin;
        $this->itms = $itms;
        $this->topEntity = $topEntity;
        $this->ctr = $ctr;
        $this->ang = $ang;
        $this->spd = $spd;
        $this->p = $p;
        $this->rad = $rad;
    }

    public function onRun(): void {
        $this->ang += $this->spd;
        $this->spd *= 0.99;

        foreach ($this->itms as $idx => $itm) {
            if ($itm->isClosed() || $itm->isFlaggedForDespawn()) {
                return;
            }
            $itemAng = $this->ang + 2 * M_PI * $idx / count($this->itms);
            $x = $this->ctr->getX() + $this->rad * cos($itemAng);
            $z = $this->ctr->getZ() + $this->rad * sin($itemAng);
            $y = $this->ctr->getY() + 1 + sin($this->ang + $idx) * 0.3;
            $itm->teleport(new Vector3($x, $y, $z));
        }

        if ($this->spd < 0.01) {
            $this->plugin->pickItem($this->itms, $this->topEntity, $this->p, $this->ctr, $this->rad);
            $this->getHandler()->cancel();
        }
    }
}