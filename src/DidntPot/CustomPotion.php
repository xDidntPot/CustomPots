<?php

namespace DidntPot;

use pocketmine\block\BlockLegacyIds;
use pocketmine\block\VanillaBlocks;
use pocketmine\color\Color;
use pocketmine\entity\effect\InstantEffect;
use pocketmine\entity\projectile\SplashPotion;
use pocketmine\event\entity\ProjectileHitBlockEvent;
use pocketmine\event\entity\ProjectileHitEvent;
use pocketmine\item\PotionType;
use pocketmine\player\Player;
use pocketmine\world\particle\PotionSplashParticle;
use pocketmine\world\sound\PotionSplashSound;
use function count;
use function round;

/**
 * These splash potions were made by Wqrro/Ghezin. I ONLY updated them to API 4.
 * Full credit goes to Wqrro/Ghezin!
 */
class CustomPotion extends SplashPotion
{
    /** @var float */
    protected $gravity = 0.07;
    /** @var float */
    protected $drag = 0.015;

    /**
     * @param int $tickDiff
     * @return bool
     */
    public function entityBaseTick(int $tickDiff = 1): bool
    {
        $owner = $this->getOwningEntity();
        if ($this->isCollided or is_null($owner)) {
            $this->flagForDespawn();
        }
        return parent::entityBaseTick($tickDiff);
    }

    /**
     * @param ProjectileHitEvent $event
     * @return void
     */
    protected function onHit(ProjectileHitEvent $event): void
    {
        $owner = $this->getOwningEntity();
        $effects = $this->getPotionEffects();
        $hasEffects = true;
        if (count($effects) === 0) {
            $colors = [new Color(0x38, 0x5d, 0xc6)];
            $hasEffects = false;
        } else {
            $colors = [];
            foreach ($effects as $effect) {
                $level = $effect->getEffectLevel();
                for ($j = 0; $j < $level; ++$j) {
                    $colors[] = $effect->getColor();
                }
            }
        }

        $this->broadcastSound(new PotionSplashSound());
        $this->getWorld()->addParticle($this->location, new PotionSplashParticle(Color::mix(...$colors)));

        if ($hasEffects) {
            if (!$this->willLinger()) {
                foreach ($this->getWorld()->getNearbyEntities($this->getBoundingBox()->expand(3, 3, 3)) as $nearby) {
                    if ($owner instanceof Player and $nearby instanceof Player and $nearby->isAlive()) {
                        $totalMultiplier = 0.580 * 1.75;
                        foreach ($this->getPotionEffects() as $effect) {
                            if (!$effect->getType() instanceof InstantEffect) {
                                $newDuration = (int)round($effect->getDuration() * 0.75 * $totalMultiplier);
                                if ($newDuration < 20) {
                                    continue;
                                }
                                $effect->setDuration($newDuration);
                                $nearby->getEffects()->add($effect);
                            } else {
                                $effect->getType()->applyEffect($nearby, $effect, $totalMultiplier, $this);
                            }
                        }
                    }
                }
            }
        } elseif ($event instanceof ProjectileHitBlockEvent and $this->getPotionType()->equals(PotionType::WATER())) {
            $blockIn = $event->getBlockHit()->getSide($event->getRayTraceResult()->getHitFace());

            if ($blockIn->getId() === BlockLegacyIds::FIRE) {
                $this->getWorld()->setBlock($blockIn, VanillaBlocks::AIR());
            }
            foreach ($blockIn->getHorizontalSides() as $horizontalSide) {
                if ($horizontalSide->getId() === BlockLegacyIds::FIRE) {
                    $this->getWorld()->setBlock($horizontalSide, VanillaBlocks::AIR());
                }
            }
        }
    }
}