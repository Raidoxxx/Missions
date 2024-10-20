<?php

namespace Missions;

use Missions\manager\MissionsManager;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\item\Item;
use pocketmine\item\StringToItemParser;
use pocketmine\player\Player;
use pocketmine\Server;

trait Utils
{
    public function copyResourcesRecursively(string $directory = "", bool $replace = false): void
    {
        $pluginFolder = Main::getInstance()->getFile() . "resources/" . $directory;

        $targetFolder = Main::getInstance()->getDataFolder() . $directory;

        if (is_dir($pluginFolder)) {
            if (!is_dir($targetFolder)) {
                mkdir($targetFolder, 0777, true);
            }

            $files = scandir($pluginFolder);
            foreach ($files as $file) {
                if ($file === "." || $file === "..") {
                    continue;
                }

                if (is_dir($pluginFolder . $file)) {
                    $this->copyResourcesRecursively($directory . $file . "/", $replace);
                } else {
                    Main::getInstance()->saveResource($directory . $file, $replace);
                }
            }
        } else {
            Server::getInstance()->getLogger()->error("Diretório '$directory' não encontrado em resources.");
        }
    }

    public static function getProgressString(Mission $mission, Player $player): string
    {
        $progress = MissionsManager::getInstance()->getMissionProgress($player, $mission);
        $target = $mission->getTypeCount();
        return "$progress/$target";
    }

    public static function getTargetString(Mission $mission): string
    {
        // Entrada:
        // "item:IRON_SWORD"
        // Saida:
        // Item: ItemName
        // Block: BlockName
        // Entity: EntityName

        $target = explode(":", $mission->getTarget());
        var_dump($target);
        $type = $target[0];

        if(count($target) < 2){
            return match ($type) {
                "distance" => "Distância",
                "message" => "Mensagem",
                default => "Unknown",
            };
        }
        $name = $target[1];

        switch ($type) {
            case "item":
                $item = StringToItemParser::getInstance()->parse($name);
                return "Item: " . $item->getName();
            case "block":
                $block = StringToItemParser::getInstance()->parse($name);
                return "Block: " . $block->getName();
            case "entity":
                return "Entity: " . $name;
            case "cause":
                return "Causa: " . self::getCauseName($name);
            default:
                return "Unknown";
        }
    }

    /**
     * Define as causas de dano e retorna seu nome.
     */
    public static function getCauseName(int $cause): string
    {
        return match ($cause) {
            EntityDamageEvent::CAUSE_CONTACT => "Contato",
            EntityDamageEvent::CAUSE_ENTITY_ATTACK => "Ataque de entidade",
            EntityDamageEvent::CAUSE_PROJECTILE => "Projétil",
            EntityDamageEvent::CAUSE_SUFFOCATION => "Sufocamento",
            EntityDamageEvent::CAUSE_FALL => "Queda",
            EntityDamageEvent::CAUSE_FIRE => "Fogo",
            EntityDamageEvent::CAUSE_FIRE_TICK => "Queimadura",
            EntityDamageEvent::CAUSE_LAVA => "Lava",
            EntityDamageEvent::CAUSE_DROWNING => "Afogamento",
            EntityDamageEvent::CAUSE_BLOCK_EXPLOSION => "Explosão de bloco",
            EntityDamageEvent::CAUSE_ENTITY_EXPLOSION => "Explosão de entidade",
            EntityDamageEvent::CAUSE_VOID => "Vácuo",
            EntityDamageEvent::CAUSE_SUICIDE => "Suicídio",
            EntityDamageEvent::CAUSE_MAGIC => "Magia",
            EntityDamageEvent::CAUSE_CUSTOM => "Customizado",
            EntityDamageEvent::CAUSE_STARVATION => "Fome",
            EntityDamageEvent::CAUSE_FALLING_BLOCK => "Bloco caindo",
            default => "Desconhecido",
        };
    }

}