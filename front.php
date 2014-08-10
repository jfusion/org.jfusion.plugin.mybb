<?php namespace JFusion\Plugins\mybb;
/**
 * @category   Plugins
 * @package    JFusion\Plugins
 * @subpackage mybb
 * @author     JFusion Team <webmaster@jfusion.org>
 * @copyright  2008 JFusion. All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.jfusion.org
 */

use JFusion\Plugin\Plugin_Front;

/**
 * JFusion Public Class for MyBB
 * For detailed descriptions on these functions please check the model.abstractpublic.php
 *
 * @category   Plugins
 * @package    JFusion\Plugins
 * @subpackage mybb
 * @author     JFusion Team <webmaster@jfusion.org>
 * @copyright  2008 JFusion. All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.jfusion.org
 */
class Front extends Plugin_Front
{
    /**
     * @return string
     */
    function getRegistrationURL() {
        return 'member.php?action=register';
    }

    /**
     * @return string
     */
    function getLostPasswordURL() {
        return 'member.php?action=lostpw';
    }

    /**
     * @return string
     */
    function getLostUsernameURL() {
        return 'member.php?action=lostpw';
    }
}
