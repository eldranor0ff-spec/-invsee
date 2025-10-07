<?php
declare(strict_types=1);

namespace InvSee;

use pocketmine\plugin\PluginBase;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use muqsit\invmenu\InvMenu;
use muqsit\invmenu\InvMenuHandler;
use muqsit\invmenu\transaction\InvMenuTransaction;
use muqsit\invmenu\transaction\InvMenuTransactionResult;

class InvSee extends PluginBase {

    protected function onEnable() : void {
        if(!InvMenuHandler::isRegistered()){
            InvMenuHandler::register($this);
        }
        $this->getLogger()->info("InvSee activé !");
    }

    public function onCommand(CommandSender $sender, Command $command, string $label, array $args) : bool {
        if(strtolower($command->getName()) !== "invsee") return false;

        if(!$sender instanceof Player){
            $sender->sendMessage("§cCommande uniquement pour les joueurs !");
            return true;
        }

        if(count($args) < 1){
            $sender->sendMessage("§eUtilisation : /invsee <joueur>");
            return true;
        }

        $target = $this->getServer()->getPlayerExact($args[0]);
        if($target === null){
            $sender->sendMessage("§cJoueur introuvable ou hors ligne !");
            return true;
        }

        $menu = InvMenu::create(InvMenu::TYPE_CHEST);
        $menu->setName("Inventaire de " . $target->getName());
        $menu->getInventory()->setContents($target->getInventory()->getContents());

        $menu->setListener(function(InvMenuTransaction $trans) : InvMenuTransactionResult {
            return $trans->continue();
        });

        $menu->setInventoryCloseListener(function(Player $viewer, $inventory) use ($target){
            if($target->isOnline()){
                $target->getInventory()->setContents($inventory->getContents());
            }
        });

        $menu->send($sender);
        $sender->sendMessage("§aOuverture de l’inventaire de §e" . $target->getName());
        return true;
    }
}
