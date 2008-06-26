<?php

/**
 * CrossAppUrlHelper.
 *
 * @package    symfony
 * @subpackage helper
 * @author     Patricio Mac Adden <pmacadden@desarrollo.cespi.unlp.edu.ar>
 * @version    SVN
 */

function _cross_app_url_generator($internal_uri = '', $app = '')
{
  # if internal_uri is a route or is absolute
  if (substr($internal_uri, 0, 7) == 'http://' || substr($internal_uri, 0, 1) == '@')
    return $internal_uri;
  else {
    # if is production environment
    if (($env = SF_ENVIRONMENT) == 'prod') {
      $env = '';
      # if is the index application
      if (!file_exists(sfConfig::get('sf_web_dir')."/$app.php"))
        $app = 'index';
    } else
      $env = "_$env";

    # calculate the absolute url
    $url = 'http://'.sfContext::getInstance()->getRequest()->getHost();
    $arr = explode('/', sfContext::getInstance()->getRequest()->getScriptName());

    for ($i = 0; $i < count($arr) - 1; $i++)
      $url .= $arr[$i].'/';

    return $url.$app.$env.'.php/'.$internal_uri;
  }
}

/**
 * Returns a routed URL based on the module/action passed as argument
 * and the routing configuration for the app application.
 *
 * <b>Examples:</b>
 * <code>
 *  echo url_for('my_module/my_action', 'frontend_dev');
 *    => /path/to/my/action
 *  echo url_for('@my_rule', frontend);
 *    => /path/to/my/action
 *    (app is ignored)
 *  echo url_for('@my_rule', 'frontend', true);
 *    => http://myapp.example.com/path/to/my/action
 *    (app is ignored)
 * </code>
 *
 * @param  string 'module/action' or '@rule' of the action
 * @param  string application name
 * @param  bool return absolute path?
 * @return string routed URL
 */
function cross_app_url_for($internal_uri, $app = '', $absolute = false)
{
  if (substr($internal_uri, 0, 1) == '@')
    return url_for($internal_uri, $absolute);
  else {
    $ret = _cross_app_url_generator($internal_uri, $app);
    if ($absolute)
      return $ret;
    else
      return substr($ret, strlen(sfContext::getInstance()->getRequest()->getUriPrefix()), strlen($ret));
  }
}

/**
 * Creates a <a> link tag of the given name using a routed URL
 * based on the module/action passed as argument and the routing configuration
 * for the app application. It's also possible to pass a string instead of a
 * module/action pair to get a link tag that just points without consideration.
 * If null is passed as a name, the link itself will become the name.
 * If an object is passed as a name, the object string representation is used.
 * One of the options serves for for creating javascript confirm alerts where
 * if you pass 'confirm' => 'Are you sure?', the link will be guarded
 * with a JS popup asking that question. If the user accepts, the link is processed,
 * otherwise not.
 *
 * <b>Options:</b>
 * - 'absolute' - if set to true, the helper outputs an absolute URL
 * - 'query_string' - to append a query string (starting by ?) to the routed url
 * - 'confirm' - displays a javascript confirmation alert when the link is clicked
 * - 'popup' - if set to true, the link opens a new browser window
 * - 'post' - if set to true, the link submits a POST request instead of GET (caution: do not use inside a form)
 *
 * <b>Note:</b> The 'popup' and 'post' options are not compatible with each other.
 *
 * <b>Examples:</b>
 * <code>
 *  echo link_to('Delete this page', 'my_module/my_action', 'frontend');
 *    => <a href="/path/to/my/action">Delete this page</a>
 *  echo link_to('Visit Hoogle', 'http://www.hoogle.com');
 *    => <a href="http://www.hoogle.com">Visit Hoogle</a>
 *  echo link_to('Delete this page', 'my_module/my_action', 'backend_dev', array('id' => 'myid', 'confirm' => 'Are you sure?', 'absolute' => true));
 *    => <a href="http://myapp.example.com/path/to/my/action" id="myid" onclick="return confirm('Are you sure?');">Delete this page</a>
 * </code>
 *
 * @param  string name of the link, i.e. string to appear between the <a> tags
 * @param  string 'module/action' or '@rule' of the action
 * @param  string application name.
 * @param  array additional HTML compliant <a> tag parameters
 * @return string XHTML compliant <a href> tag
 * @see    url_for
 */
function cross_app_url_link_to($name = '', $internal_uri = '', $app = '', $options = array())
{
  return link_to($name, _cross_app_url_generator($internal_uri, $app), $options);
}

/**
 * If the condition passed as first argument is true,
 * creates a <a> link tag of the given name using a routed URL
 * based on the module/action passed as argument and the routing configuration
 * for the app aplication. If the condition is false, the given name
 * is returned between <span> tags
 *
 * <b>Options:</b>
 * - 'tag' - the HTML tag that must enclose the name if the condition is false, defaults to <span>
 * - 'absolute' - if set to true, the helper outputs an absolute URL
 * - 'query_string' - to append a query string (starting by ?) to the routed url
 * - 'confirm' - displays a javascript confirmation alert when the link is clicked
 * - 'popup' - if set to true, the link opens a new browser window 
 * - 'post' - if set to true, the link submits a POST request instead of GET (caution: do not use inside a form)
 *
 * <b>Examples:</b>
 * <code>
 *  echo link_to_if($user->isAdministrator(), 'Delete this page', 'my_module/my_action', 'frontend');
 *    => <a href="/path/to/my/action">Delete this page</a>
 *  echo link_to_if(!$user->isAdministrator(), 'Delete this page', 'my_module/my_action', 'backend_dev');
 *    => <span>Delete this page</span>
 * </code>
 *
 * @param  bool condition
 * @param  string name of the link, i.e. string to appear between the <a> tags
 * @param  string 'module/action' or '@rule' of the action
 * @param  string application name
 * @param  array additional HTML compliant <a> tag parameters
 * @return string XHTML compliant <a href> tag or name
 * @see    link_to
 */
function cross_app_url_link_to_if($condition, $name = '', $internal_uri = '', $app = '', $options = array())
{
  return link_to_if($condition, $name, _cross_app_url_generator($internal_uri, $app), $options);
}

/**
 * If the condition passed as first argument is false,
 * creates a <a> link tag of the given name using a routed URL
 * based on the module/action passed as argument and the routing configuration
 * for the app application. If the condition is true, the given name is
 * returned between <span> tags.
 *
 * <b>Options:</b>
 * - 'tag' - the HTML tag that must enclose the name if the condition is true, defaults to <span>
 * - 'absolute' - if set to true, the helper outputs an absolute URL
 * - 'query_string' - to append a query string (starting by ?) to the routed url
 * - 'confirm' - displays a javascript confirmation alert when the link is clicked
 * - 'popup' - if set to true, the link opens a new browser window 
 * - 'post' - if set to true, the link submits a POST request instead of GET (caution: do not use inside a form)
 *
 * <b>Examples:</b>
 * <code>
 *  echo link_to_unless($user->isAdministrator(), 'Delete this page', 'my_module/my_action', 'frontend');
 *    => <span>Delete this page</span>
 *  echo link_to_unless(!$user->isAdministrator(), 'Delete this page', 'my_module/my_action', 'backend_dev');
 *    => <a href="/path/to/my/action">Delete this page</a>
 * </code>
 *
 * @param  bool condition
 * @param  string name of the link, i.e. string to appear between the <a> tags
 * @param  string 'module/action' or '@rule' of the action
 * @param  string application name
 * @param  array additional HTML compliant <a> tag parameters
 * @return string XHTML compliant <a href> tag or name
 * @see    link_to
 */
function cross_app_url_link_to_unless($condition, $name = '', $internal_uri = '', $app = '', $options = array())
{
  return link_to_unless($condition, $name, _cross_app_url_generator($internal_uri, $app), $options);
}

/**
 * Creates an <input> button tag of the given name pointing to a routed URL
 * based on the module/action passed as argument and the routing configuration
 * for the app application. The syntax is similar to the one of cross_app_url_link_to.
 *
 * <b>Options:</b>
 * - 'absolute' - if set to true, the helper outputs an absolute URL
 * - 'query_string' - to append a query string (starting by ?) to the routed url
 * - 'confirm' - displays a javascript confirmation alert when the button is clicked
 * - 'popup' - if set to true, the button opens a new browser window 
 * - 'post' - if set to true, the button submits a POST request instead of GET (caution: do not use inside a form)
 *
 * <b>Examples:</b>
 * <code>
 *  echo button_to('Delete this page', 'my_module/my_action', 'frontend');
 *    => <input value="Delete this page" type="button" onclick="document.location.href='/path/to/my/action';" />
 * </code>
 *
 * @param  string name of the button
 * @param  string 'module/action' or '@rule' of the action
 * @param  string application name
 * @param  array additional HTML compliant <input> tag parameters
 * @return string XHTML compliant <input> tag
 * @see    url_for, link_to
 */
function cross_app_url_button_to($name = '', $internal_uri = '', $app = '', $options = array())
{
  return button_to($name, _cross_app_url_generator($internal_uri, $app), $options);
}

?>
