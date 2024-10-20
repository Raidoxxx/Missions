<?php

namespace Missions;

use pocketmine\plugin\PluginBase;
use SOFe\AwaitStd\AwaitStd;

class Main extends PluginBase
{
    use Utils;
    private static Main $instance;

    private AwaitStd $std ;

    public function onEnable(): void
    {
        self::$instance = $this;

        $this->std = AwaitStd::init($this);
        $this->getLogger()->info("§eMissions | Loading Database...");

        new Database($this);
        $db = Database::getInstance()->getDb();
        $db->executeGeneric("init.player_quest_progress");
        $db->waitAll();
    }

    public static function getInstance(): Main
    {
        return self::$instance;
    }


    /**
     * @return AwaitStd
     */
    public
    function getStd() : AwaitStd
    {
        return $this->std;
    }
}

