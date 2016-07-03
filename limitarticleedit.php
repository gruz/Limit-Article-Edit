<?php
/**
 * @package        Limit Article Edit
 * @copyright (C) 2010 by Source Coast - All rights reserved
 * http://www.sourcecoast.com
 * http://www.cmsmarket.com
 * http://www.covertapps.com
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */


// no direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.plugin.plugin');

class plgSystemLimitArticleEdit extends JPlugin
{

	function onAfterRoute()	{
		// Some basic variables to work
		$app	= JFactory::getApplication();
		if (JFactory::getUser()->guest) {return true;}
		if ($app->isAdmin()) {return true;}

		$jinput = $app->input;
		$session = JFactory::getSession();

		$option = $jinput->getVar('option');
		$namespace = 'plgSystemLimitArticleEdit';

		if ($jinput->get('option') !== 'com_dump') { // to avoid com_dump problems
			$prev_link = $session->get('previous_link', '', $namespace);

			//~ // Here I "unparse" the Joomla SEF url to get the internal joomla URL
			//~ JURI::current();// It's very strange, but without this line at least Joomla 3 fails to fulfill the task
			//~ $router = JSite::getRouter();// get router
			//~ $inst = clone JURI::getInstance();
			//~ $query = $router->parse($inst); // Get the real joomla query as an array - parse current joomla link
			//~ $url = 'index.php?'.JURI::getInstance()->buildQuery($query);

			// build the JInput object
			$jinput = JFactory::getApplication()->input;
			// retrieve the array of values from the request (stored in the application environment) to form the query
			$uriQuery = $jinput->getArray();
			// build the the query as a string
			$url = 'index.php?' . JUri::buildQuery($uriQuery);

			$session->set('previous_link', $url, $namespace);
		}

		$user = JFactory::getUser();
		$task = $jinput->getVar('task');
		$id = $jinput->getInt('a_id');

		//If we are not trying to edit or save an article - then leave
		if(!($option == "com_content" && ($task == "article.edit" || $task == "article.save"))) { return true;}


		$canEditOwn = $user->authorise('core.edit.own', 'com_content.article.'.$id);
		$canEdit = $user->authorise('core.edit', 'com_content.article.'.$id);
		$canEditState = $user->authorise('core.edit.state', 'com_content.article.'.$id);
		if (!($canEditOwn || $canEdit)) { return true;}//If the user is not allowed to edit the article, then leave!
		//If the user can edit the article, but cannot edit state, then check the state and do not allow to edit something except an unpublished article
		if ($canEditState) {return;}


		//if (!$user->authorise('core.edit.state', 'com_content')) {
		//	return JError::raiseWarning(403, JText::_('JERROR_ALERTNOAUTHOR'));
		//}
		// The user is either accessing the editing or saving an article.


		// The user is not a publisher or higher
		$article = JTable::getInstance("content");
		$article->load($id);

		// Get the state of the article
		//$dbo = &JFactory::getDBO();
		//$query = "SELECT state ".
		//	"FROM #__content ".
		//	"WHERE id = ".$dbo->quote($id);
		//$dbo->setQuery($query);
		//$state = $dbo->loadResult();


		if($article->state != 0)
		{
			JError::raiseWarning(403, JText::_('JLIB_APPLICATION_ERROR_EDIT_ITEM_NOT_PERMITTED'));
			$app->redirect($prev_link);
		}
		return;

	}
}
