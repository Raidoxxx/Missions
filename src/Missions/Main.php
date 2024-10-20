<?php

namespace Missions;

use Missions\commands\MissionsCommand;
use Missions\listeners\PlayerListeners;
use Missions\manager\DatabaseManager;
use Missions\manager\MissionsManager;
use Missions\tasks\MissionTask;
use pocketmine\lang\KnownTranslationFactory as l10n;
use pocketmine\permission\DefaultPermissionNames as Names;
use pocketmine\permission\DefaultPermissions;
use pocketmine\permission\Permission;
use pocketmine\plugin\PluginBase;
use pocketmine\Server;
use SQLite3;

class Main extends PluginBase
{

    use Utils;
    private static Main $instance;
    private MissionsManager $missionsManager;
    private DatabaseManager $databaseManager;
    private $database;

    public function onEnable(): void
    {
        self::$instance = $this;
        $this->saveDefaultConfig();
        $this->copyResourcesRecursively("missions/");
        try {
            $this->loadDatabase();
        } catch (\Exception $e) {
            Server::getInstance()->getLogger()->error("Erro ao carregar o banco de dados: " . $e->getMessage());
        } finally {
            $this->databaseManager = new DatabaseManager($this->getDatabase());
            $this->missionsManager = new MissionsManager($this->databaseManager);
        }

        $consoleRoot = DefaultPermissions::registerPermission(new Permission(DefaultPermissions::ROOT_CONSOLE, l10n::pocketmine_permission_group_console()));
        $operatorRoot = DefaultPermissions::registerPermission(new Permission(DefaultPermissions::ROOT_OPERATOR, l10n::pocketmine_permission_group_operator()), [$consoleRoot]);

        DefaultPermissions::registerPermission(new Permission("missions.command", "Permite o uso do comando /missions", [$operatorRoot]), [$operatorRoot]);

        $this->loadListeners();
        $this->loadCommands();

        $this->getScheduler()->scheduleRepeatingTask(new MissionTask(), 20);
        Server::getInstance()->getPluginManager()->registerEvents(new PlayerListeners(), $this);
    }

    public function loadCommands(): void
    {
        $cmds = [
            new MissionsCommand()
        ];

        array_walk($cmds, fn($cmd) => Server::getInstance()->getCommandMap()->register($cmd->getName(), $cmd));
    }
    public function loadListeners(): void
    {
        $listeners = [
            new PlayerListeners($this->missionsManager)
        ];

        array_walk($listeners, fn($listener) => Server::getInstance()->getPluginManager()->registerEvents($listener, $this));
    }

    public function loadDatabase(): void
    {
        $config_array = $this->getConfig()->get("database");

        if(!is_dir(Server::getInstance()->getDataPath() . "plugin_data" . DIRECTORY_SEPARATOR . $this->getName() . DIRECTORY_SEPARATOR . "database")){
            mkdir(Server::getInstance()->getDataPath() . "plugin_data" . DIRECTORY_SEPARATOR . $this->getName() . DIRECTORY_SEPARATOR . "database");
        }

        if($config_array["type"] === "sqlite"){
            if(is_null($this->database)){

                $this->database = new SQLite3(Server::getInstance()->getDataPath() . "plugin_data" . DIRECTORY_SEPARATOR . $this->getName() . DIRECTORY_SEPARATOR . "database" . DIRECTORY_SEPARATOR . $config_array["database"].".db");
            }

            Server::getInstance()->getLogger()->info("Database carregado com sucesso, utilizando SQLite3");
        }

        if($config_array["type"] === "mysql"){
            $this->database = new \mysqli($config_array["host"], $config_array["username"], $config_array["password"], $config_array["database"], $config_array["port"]);
            Server::getInstance()->getLogger()->info("Database carregado com sucesso, utilizando MySQL");
        }
    }

    public static function getInstance(): Main
    {
        return self::$instance;
    }

    public function getMissionsManager(): MissionsManager
    {
        return $this->missionsManager;
    }

    public function getDatabaseManager(): DatabaseManager
    {
        return $this->databaseManager;
    }

    public function getDatabase(): ?SQLite3
    {
        if(is_null($this->database)){
            return $this->initDatabase();
        }

        return $this->database;
    }

    public function initDatabase(): ?SQLite3
    {
        $config_array = $this->getConfig()->get("database");
        match ($config_array["type"]) {
            "sqlite" => $this->database = new SQLite3($config_array["path"]),
            default => $this->database = null
        };

        return $this->database;
    }

}

