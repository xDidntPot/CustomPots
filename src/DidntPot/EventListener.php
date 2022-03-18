<?php

namespace DidntPot;

use pocketmine\data\bedrock\PotionTypeIdMap;
use pocketmine\data\bedrock\PotionTypeIds;
use pocketmine\entity\Location;
use pocketmine\event\entity\ProjectileLaunchEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerItemUseEvent;
use pocketmine\item\VanillaItems;
use pocketmine\player\Player;
use pocketmine\world\sound\ThrowSound;

class EventListener implements Listener
{
    /**
     * @param PlayerInteractEvent $ev
     * @return void
     */
    public function onInteract(PlayerInteractEvent $ev): void
    {
        if ($ev->getPlayer()->getInventory()->getItemInHand()->getMeta() === 22) {
            $this->sendPotion($ev->getPlayer());
            $ev->cancel();
        }
    }

    /**
     * @param Player $player
     * @return void
     */
    protected function sendPotion(Player $player): void
    {
        $location = $player->getLocation();

        $pot = new CustomPotion(
            Location::fromObject(
                $player->getEyePos(),
                $location->getWorld(),
                $location->getYaw(),
                $location->getPitch()
            ),
            $player,
            PotionTypeIdMap::getInstance()->fromId(PotionTypeIds::STRONG_HEALING)
        );

        $ev = new ProjectileLaunchEvent($pot);
        $ev->call();

        if ($ev->isCancelled()) {
            $pot->flagForDespawn();
        }

        $pot->spawnToAll();
        $location->getWorld()->addSound($location, new ThrowSound());

        if (!$player->isCreative()) $player->getInventory()->setItemInHand(VanillaItems::AIR());
    }

    /**
     * @param PlayerItemUseEvent $ev
     * @return void
     */
    public function onItemUse(PlayerItemUseEvent $ev): void
    {
        if ($ev->getPlayer()->getInventory()->getItemInHand()->getMeta() === 22) {
            $this->sendPotion($ev->getPlayer());
            $ev->cancel();
        }
    }
}
