<?php

namespace DidntPot;

use pocketmine\data\bedrock\EntityLegacyIds;
use pocketmine\data\bedrock\PotionTypeIdMap;
use pocketmine\data\bedrock\PotionTypeIds;
use pocketmine\entity\EntityDataHelper;
use pocketmine\entity\EntityFactory;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\plugin\PluginBase;
use pocketmine\world\World;
use UnexpectedValueException;

class Loader extends PluginBase
{
    public function onEnable(): void
    {
        EntityFactory::getInstance()->register(CustomPotion::class, function (World $world, CompoundTag $nbt): CustomPotion {
            $potionType = PotionTypeIdMap::getInstance()->fromId($nbt->getShort("PotionId", PotionTypeIds::WATER));
            if ($potionType === null) {
                throw new UnexpectedValueException("No such potion type");
            }
            return new CustomPotion(EntityDataHelper::parseLocation($nbt, $world), null, $potionType, $nbt);
        }, ["ThrownPotion", "minecraft:potion", "thrownpotion"], EntityLegacyIds::SPLASH_POTION);

        $this->getServer()->getPluginManager()->registerEvents(new EventListener(), $this);
    }
}