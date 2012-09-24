<?php
/**
 * A MySQL to Redis data-push Task for FuelPHP
 *
 * @package    Siphon
 * @version    0.1
 * @author     Michael Crumm
 * @license    MIT License
 * @copyright  2012 Michael Crumm
 * @link       http://crumm.net
 */

Autoloader::add_classes(array(
	'Fuel\\Tasks\\Siphon'=> __DIR__ . '/tasks/siphon.php',
));