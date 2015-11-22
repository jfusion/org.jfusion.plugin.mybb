<?php namespace JFusion\Plugins\mybb\Platform\Joomla;
/**
 * @category   Plugins
 * @package    JFusion\Plugins
 * @subpackage mybb
 * @author     JFusion Team <webmaster@jfusion.org>
 * @copyright  2008 JFusion. All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.jfusion.org
 */

use JFusion\Factory;
use JFusion\Framework;
use JFusion\Plugin\Platform\Joomla;

use Psr\Log\LogLevel;

use Exception;

/**
 * JFusion Platform Class for MyBB
 * For detailed descriptions on these functions please check the Joomla
 *
 * @category   Plugins
 * @package    JFusion\Plugins
 * @subpackage mybb
 * @author     JFusion Team <webmaster@jfusion.org>
 * @copyright  2008 JFusion. All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.jfusion.org
 */
class Platform extends Joomla
{
    /**
     * @param int $threadid
     * @return string
     */
    function getThreadURL($threadid) {
        return 'showthread.php?tid=' . $threadid;
    }

    /**
     * @param int $threadid
     * @param int $postid
     * @return string
     */
    function getPostURL($threadid, $postid) {
        return 'showthread.php?tid=' . $threadid . '&amp;pid=' . $postid . '#pid' . $postid;
    }

    /**
     * @param int $userid
     *
     * @return int|string
     */
    function getProfileURL($userid) {
        return 'member.php?action=profile&uid=' . $userid;
    }

    /**
     * @param array $usedforums
     * @param string $result_order

     * @return array
     */
    function getActivityQuery($usedforums, $result_order) {
	    $query = array();
	    try
	    {
		    $db = Factory::getDatabase($this->getJname());

	        $where = (!empty($usedforums)) ? 'a.fid IN (' . $db->quote($usedforums) . ')' : '';

		    $q = $db->getQuery(true)
			    ->select('a.tid AS threadid, b.pid AS postid, b.username, b.uid AS userid, a.subject, b.dateline')
			    ->from('#__threads AS a')
			    ->innerJoin('#__posts as b ON a.firstpost = b.pid')
			    ->where($db->quote($where))
			    ->order('a.lastpost ' . $db->quote($result_order));

		    $query[self::LAT . '0'] = (string)$q;

		    $q = $db->getQuery(true)
			    ->select('a.tid AS threadid, b.pid AS postid, b.username, b.uid AS userid, a.subject, b.dateline')
			    ->from('#__threads AS a')
			    ->innerJoin('#__posts as b ON a.tid = b.tid AND a.lastpost = b.dateline AND a.lastposteruid = b.uid')
			    ->where($db->quote($where))
			    ->order('a.lastpost ' . $db->quote($result_order));

		    $query[self::LAT . '1'] = (string)$q;

		    $q = $db->getQuery(true)
			    ->select('a.tid AS threadid, b.pid AS postid, b.username, b.uid AS userid, b.subject, b.dateline, b.message AS body')
			    ->from('#__threads AS a')
			    ->innerJoin('#__posts as b ON a.firstpost = b.pid')
			    ->where($db->quote($where))
			    ->order('a.dateline ' . $db->quote($result_order));

		    $query[self::LCT] = (string)$q;

		    $q = $db->getQuery(true)
			    ->select('a.tid AS threadid, a.pid AS postid, a.username, a.uid AS userid, a.subject, a.dateline, a.message AS body')
			    ->from('#__posts AS a')
			    ->where($db->quote($where))
			    ->order('a.dateline ' . $db->quote($result_order));

		    $query[self::LCP] = (string)$q;
	    } catch (Exception $e) {
		    Framework::raise(LogLevel::ERROR, $e, $this->getJname());
	    }
        return $query;
    }

    /**
     * @param int $threadid
     * @return object
     */
    function getThread($threadid) {
	    try {
		    $db = Factory::getDatabase($this->getJname());

		    $query = $db->getQuery(true)
			    ->select('tid AS threadid, fid AS forumid, firstpost AS postid')
			    ->from('#__threads')
		        ->where('tid = ' . (int)$threadid);

		    $db->setQuery($query);
		    $results = $db->loadObject();
	    } catch (Exception $e) {
		    Framework::raise(LogLevel::ERROR, $e, $this->getJname());
		    $results = null;
	    }
        return $results;
    }

    /**
     * @param object $existingthread
     * @return int
     */
    function getReplyCount($existingthread) {
	    try {
		    $db = Factory::getDatabase($this->getJname());

		    $query = $db->getQuery(true)
			    ->select('replies')
			    ->from('#__threads')
			    ->where('tid = ' . (int)$existingthread->threadid);

		    $db->setQuery($query);
		    $result = $db->loadResult();
	    } catch (Exception $e) {
		    Framework::raise(LogLevel::ERROR, $e, $this->getJname());
		    $result = 0;
	    }
        return $result;
    }

    /**
     * @return array
     */
    function getForumList() {
	    try {
		    //get the connection to the db
		    $db = Factory::getDatabase($this->getJname());

		    $query = $db->getQuery(true)
			    ->select('fid as id, name')
			    ->from('#__forums');

		    $db->setQuery($query);
		    //getting the results
		    return $db->loadObjectList();
	    } catch (Exception $e) {
		    Framework::raise(LogLevel::ERROR, $e, $this->getJname());
		    return array();
	    }
    }

    /**
     * @param int $userid
     * @return array
     */
    function getPrivateMessageCounts($userid) {
	    try {
		    if ($userid) {
			    //get the connection to the db
			    $db = Factory::getDatabase($this->getJname());

			    $query = $db->getQuery(true)
				    ->select('totalpms, unreadpms')
				    ->from('#__users')
				    ->where('uid = ' . (int)$userid);
			    // read unread count
			    $db->setQuery($query);

			    $pminfo = $db->loadObject();
			    return array('unread' => $pminfo->unreadpms, 'total' => $pminfo->totalpms);
		    }
	    } catch (Exception $e) {
		    Framework::raise(LogLevel::ERROR, $e, $this->getJname());
	    }
        return array('unread' => 0, 'total' => 0);
    }

    /**
     * @return string
     */
    function getPrivateMessageURL() {
        return 'private.php';
    }

    /**
     * @return string
     */
    function getViewNewMessagesURL() {
        return 'search.php?action=getnew';
    }

    /**
     * @param int $userid
     * @return string
     */
    function getAvatar($userid) {
	    $url = '';
	    try {
		    //get the connection to the db
		    $db = Factory::getDatabase($this->getJname());
		    // read unread count

		    $query = $db->getQuery(true)
			    ->select('avatar')
			    ->from('#__users')
			    ->where('uid = ' . (int)$userid);

		    $db->setQuery($query);
		    $avatar = $db->loadResult();
		    if (!empty($avatar)) {
			    $avatar = substr($avatar, 2);
			    if (!empty($avatar)) {
				    $url = $this->params->get('source_url') . $avatar;
			    }
		    }
	    } catch (Exception $e) {
		    Framework::raise(LogLevel::ERROR, $e, $this->getJname());
	    }
        return $url;
    }

	/* temp disabled native frameless
	function getBuffer() {
		// Get the path
		$source_path = $this->params->get('source_path');
		//get the filename
		$jfile = \JFusion\Factory::getApplication()->input->get('jfile', 'index.php');
		if (!$jfile) {
			$jfile = 'index.php';
		}
		//combine the path and filename
		$index_file = $source_path . $jfile;
		if (!is_file($index_file)) {
			\JFusion\Framework::raise(LogLevel::WARNING, 'The path to the requested does not exist', $this->getJname());
		} else {
			//set the current directory to MyBB
			chdir($source_path);
			// set scope for variables required later
			global $mybb, $theme, $templates, $db, $lang, $plugins, $session, $cache;
			global $debug, $templatecache, $templatelist, $maintimer, $globaltime, $parsetime;
			// Get the output
			ob_start();
			include_once ($index_file);
			$this->data->buffer = ob_get_contents();
			ob_end_clean();
			//change the current directory back to Joomla.
			chdir(JPATH_SITE);
		}
	}
*/

	function parseBody() {
		$regex_body = array();
		$replace_body = array();
		$callback_body = array();

		$regex_body[] = '#action="(.*?)"(.*?)>#m';
		$replace_body[] = '';//$this->fixAction("index.php$1","$2","' . $this->data->baseURL . '")';
		$callback_body[] = 'fixAction';

		$regex_body[]	= '#(?<=href=["\'])[./|/](.*?)(?=["\'])#mS';
		$replace_body[] = '';
		$callback_body[] = 'fixUrl';

		$regex_body[] = '#(?<=href=["\'])(?!\w{0,10}://|\w{0,10}:)(.*?)(?=["\'])#mSi';
		$replace_body[] = '';
		$callback_body[] = 'fixUrl';

		$regex_body[]	= '#(?<=href=["\'])' . $this->data->integratedURL . '(.*?)(?=["\'])#m';
		$replace_body[] = '';
		$callback_body[] = 'fixUrl';

		$regex_body[]	= '#(?<=href=\\\")' . $this->data->integratedURL . '(.*?)(?=\\\")#mS';
		$replace_body[] = '';
		$callback_body[] = 'fixUrl';

		$regex_body[] = '#(src)=["\'][./|/](.*?)["\']#mS';
		$replace_body[] = '$1="' . $this->data->integratedURL . '$2"';
		$callback_body[] = '';

		$regex_body[] = '#(src)=["\'](?!\w{0,10}://|\w{0,10}:)(.*?)["\']#mS';
		$replace_body[] = '$1="' . $this->data->integratedURL . '$2"';
		$callback_body[] = '';

		foreach ($regex_body as $k => $v) {
			//check if we need to use callback
			if(!empty($callback_body[$k])){
				$this->data->body = preg_replace_callback($regex_body[$k], array(&$this, $callback_body[$k]), $this->data->body);
			} else {
				$this->data->body = preg_replace($regex_body[$k], $replace_body[$k], $this->data->body);
			}
		}
	}

	function parseHeader() {
		static $regex_header, $replace_header;
		if (!$regex_header || !$replace_header) {
			// Define our preg arrays
			$regex_header = array();
			$replace_header = array();
			$callback_header = array();

			//fix for URL redirects
			$regex_header[] = '#(?<=<meta http-equiv="refresh" content=")(.*?)(?=")#mi';
			$replace_header[] = ''; //$this->fixRedirect("$1","' . $this->data->baseURL . '")';
			$callback_header[] = 'fixRedirect';
		}
		foreach ($regex_header as $k => $v) {
			//check if we need to use callback
			if(!empty($callback_header[$k])) {
				$this->data->header = preg_replace_callback($regex_header[$k], array(&$this, $callback_header[$k]), $this->data->header);
			} else {
				$this->data->header = preg_replace($regex_header[$k], $replace_header[$k], $this->data->header);
			}
		}
	}
}
