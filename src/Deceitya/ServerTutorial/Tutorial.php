<?php

namespace Deceitya\ServerTutorial;

use pocketmine\Server;
use pocketmine\level\Location;

class Tutorial
{
    /** @var string */
    private $message;
    /** @var int */
    private $time;
    /** @var Location */
    private $location;

    public function __construct(string $message, int $time, Location $location)
    {
        $this->message = $message;
        $this->time = $time;
        $this->location = $location;
    }

    public static function fromData(array $data): self
    {
        $message = $data['message'];
        $time = $data['time'];
        $server = Server::getInstance();
        $location = new Location(
            $data['x'],
            $data['y'],
            $data['z'],
            $data['yaw'],
            $data['pitch'],
            $server->loadLevel($data['world']) ? $server->getLevelByName($data['world']) : $server->getDefaultLevel()
        );

        return new self($message, $time, $location);
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @return integer
     */
    public function getTime(): int
    {
        return $this->time;
    }

    /**
     * @return Location
     */
    public function getLocation(): Location
    {
        return $this->location;
    }

    /**
     * @return array
     */
    public function toSaveFormat(): array
    {
        $data = [];
        $data['message'] = $this->message;
        $data['time'] = $this->time;
        $data['world'] = $this->location->getLevel()->getName();
        $data['x'] = $this->location->getX();
        $data['y'] = $this->location->getY();
        $data['z'] = $this->location->getZ();
        $data['yaw'] = $this->location->getYaw();
        $data['pitch'] = $this->location->getPitch();

        return $data;
    }
}
