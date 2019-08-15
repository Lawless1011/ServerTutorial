<?php

namespace Deceitya\ServerTutorial;

use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use pocketmine\Player;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\scheduler\ClosureTask;

use Deceitya\ServerTutorial\Tutorial;

class ServerTutorialPlugin extends PluginBase
{
    /** @var Tutorial[] */
    private $tutorials;

    public function onEnable()
    {
        $this->saveResource('tutorial.json');
        foreach ((new Config($this->getDataFolder() . 'tutorial.json', Config::JSON))->getAll() as $tutorial) {
            $this->tutorials[] = Tutorial::fromData($tutorial);
        }
    }

    public function onDisable()
    {
        $config = new Config($this->getDataFolder() . 'tutorial.json', Config::JSON);
        $config->setJsonOptions(JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

        $all = [];
        foreach ($this->tutorials as $tutorial) {
            $all[] = $tutorial->toSaveFormat();
        }

        $config->setAll($all);
        $config->save();
    }

    public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool
    {
        if (!isset($args[0])) {
            return false;
        }
        if (!($sender instanceof Player)) {
            return false;
        }

        switch ($args[0]) {
            case 'set':
                if (count($args) < 3) {
                    return false;
                }
                if (!$sender->isOp()) {
                    return true;
                }

                $this->tutorials[] = new Tutorial($args[1], $args[2], $sender->getLocation());
                $sender->sendMessage('チュートリアル地点を設定しました。');

                return true;
            case 'start':
                $pos = $sender->getPosition();
                $mode = $sender->getGamemode();

                $sender->setGamemode(Player::SPECTATOR);
                $sender->setImmobile(true);

                $time = 0;
                foreach ($this->tutorials as $tutorial) {
                    $this->getScheduler()->scheduleDelayedTask(new ClosureTask(
                        function (int $currentTick) use ($sender, $tutorial): void {
                            $sender->teleport($tutorial->getLocation());
                            $sender->sendMessage($tutorial->getMessage());
                        }
                    ), 20 * $time);

                    $time += $tutorial->getTime();
                }

                $this->getScheduler()->scheduleDelayedTask(new ClosureTask(
                    function (int $currentTick) use ($sender, $pos, $mode): void {
                        $sender->teleport($pos);
                        $sender->setGamemode($mode);
                        $sender->setImmobile(false);
                    }
                ), 20 * $time);

                return true;
            default:
                return false;
        }

        return false;
    }
}
