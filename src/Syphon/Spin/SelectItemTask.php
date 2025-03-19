<?php
namespace Syphon\Spin;

use pocketmine\scheduler\Task;
use pocketmine\entity\object\ItemEntity;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\world\particle\HappyVillagerParticle;
use pocketmine\world\particle\HugeExplodeParticle;
use pocketmine\world\sound\AnvilUseSound;
use pocketmine\world\sound\ExplodeSound;
use pocketmine\scheduler\ClosureTask;

class SelectItemTask extends Task {
    private $plugin;
    private $chosen;
    private $topEntity;
    private $startPos;
    private $upPos;
    private $downPos;
    private $stepsUp;
    private $stepsDown;
    private $p;
    private $tick = 0;
    private $phase = "up";
    private $done = false;

    public function __construct(Main $plugin, ItemEntity $chosen, ItemEntity $topEntity, Vector3 $startPos, Vector3 $upPos, Vector3 $downPos, int $stepsUp, int $stepsDown, Player $p) {
        $this->plugin = $plugin;
        $this->chosen = $chosen;
        $this->topEntity = $topEntity;
        $this->startPos = $startPos;
        $this->upPos = $upPos;
        $this->downPos = $downPos;
        $this->stepsUp = $stepsUp;
        $this->stepsDown = $stepsDown;
        $this->p = $p;
    }

    public function onRun(): void {
        if ($this->phase === "up" && $this->tick <= $this->stepsUp && !$this->chosen->isClosed()) {
            $prog = $this->tick / $this->stepsUp;
            $smooth = (1 - cos(M_PI * $prog)) / 2;
            $x = $this->startPos->x + ($this->upPos->x - $this->startPos->x) * $smooth;
            $y = $this->startPos->y + ($this->upPos->y - $this->startPos->y) * $smooth;
            $z = $this->startPos->z + ($this->upPos->z - $this->startPos->z) * $smooth;
            $this->chosen->teleport(new Vector3($x, $y, $z));
            $this->p->getWorld()->addParticle(new Vector3($x, $y, $z), new HappyVillagerParticle());
            $this->tick++;
            if ($this->tick > $this->stepsUp) {
                $this->phase = "down";
                $this->tick = 0;
                $this->topEntity->flagForDespawn();
            }
        } elseif ($this->phase === "down" && $this->tick <= $this->stepsDown && !$this->chosen->isClosed()) {
            $prog = $this->tick / $this->stepsDown;
            $smooth = (1 - cos(M_PI * $prog)) / 2;
            $x = $this->upPos->x + ($this->downPos->x - $this->upPos->x) * $smooth;
            $y = $this->upPos->y + ($this->downPos->y - $this->upPos->y) * $smooth;
            $z = $this->upPos->z + ($this->downPos->z - $this->upPos->z) * $smooth;
            $this->chosen->teleport(new Vector3($x, $y, $z));
            $this->chosen->setRotation($this->tick * 10, 0);
            $this->p->getWorld()->addParticle(new Vector3($x, $y, $z), new HappyVillagerParticle());
            $this->tick++;
        } elseif (!$this->done && !$this->chosen->isClosed()) {
            $this->chosen->setHasGravity(false);
            $this->chosen->setNameTagVisible(true);
            $this->chosen->setNameTagAlwaysVisible(true);
            $cfg = $this->plugin->getConfig();
            $this->chosen->setNameTag($cfg->get("selected-nametag-prefix") . $this->chosen->getItem()->getName());
            $this->p->getInventory()->addItem($this->chosen->getItem()->setCount(1));
            for ($i = 0; $i < 15; $i++) {
                $this->p->getWorld()->addParticle($this->downPos, new HugeExplodeParticle());
            }
            $this->p->getWorld()->addSound($this->downPos, new ExplodeSound());
            $this->p->getWorld()->addSound($this->downPos, new AnvilUseSound());
            for ($i = 0; $i < 5; $i++) {
                $chosen = $this->chosen;
                $downPos = $this->downPos;
                $this->plugin->getScheduler()->scheduleDelayedTask(new ClosureTask(
                    function() use ($chosen, $downPos) {
                        if (!$chosen->isClosed()) {
                            $offset = (mt_rand(-10, 10) / 100);
                            $chosen->teleport($downPos->add($offset, 0, $offset));
                        }
                    }
                ), $i * 2);
            }
            $chosen = $this->chosen;
            $this->plugin->getScheduler()->scheduleDelayedTask(new ClosureTask(
                function() use ($chosen) {
                    if (!$chosen->isClosed()) {
                        $chosen->flagForDespawn();
                    }
                }
            ), 60);
            $this->done = true;
            $this->getHandler()->cancel();
        }
    }
}