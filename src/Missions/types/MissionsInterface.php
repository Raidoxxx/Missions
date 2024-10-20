<?php

namespace Missions\types;

use pocketmine\player\Player;

interface MissionInterface {
    public function getName(): string;
    public function checkProgress(Player $player): bool;
    public function getDescription(): string;
    public function getReward(): array;
}
