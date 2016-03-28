<?php namespace CSI\BbCodeMediaWikiGetUserInfo\BbCode\Formatter;

/**
 * Class Base
 * @package CSI\BbCodeMediaWikiGetUserInfo\BbCode\Formatter
 */
class Base
{
  /**
   * @param array $tag
   * @param array $rendererStates
   * @param \XenForo_BbCode_Formatter_Base $formatter
   * @return mixed
   */
  public static function getBbCodeMediaWikiGetUserInfo(array $tag, array $rendererStates, \XenForo_BbCode_Formatter_Base $formatter)
  {
    $xenOptions = \XenForo_Application::get('options');
    $xenVisitor = \XenForo_Visitor::getInstance();
    $tagOption = array_map('trim', explode('|', $tag['option']));

    if (count($tagOption) > 1) {
      $optDefault = $tagOption[0];
    } else {
      $optDefault = $tag['option'];
    }

    $tagContent = $formatter->renderSubTree($tag['children'], $rendererStates);

    if (!$xenOptions->csiXF_bbCode_ADFBB123_onoff || !$xenOptions->csiXF_bbCode_ADFBB123_urlApi || !preg_match('#^(\w+)$#', $tagContent)) {
      return $formatter->renderInvalidTag($tag, $rendererStates);
    }

    $tagContent = rawurlencode($tagContent);

    $getData = $xenOptions->csiXF_bbCode_ADFBB123_urlApi . '?action=query&list=users&usprop=' . $xenOptions->csiXF_bbCode_ADFBB123_usprop . '&ususers=' . $tagContent . '&format=json';
    $decodeData = json_decode(file_get_contents($getData), true);
    $queryData = $decodeData['query']['users'];

    foreach ($queryData as $user) {
      if (!isset($user['userid'])) {
        return $formatter->renderInvalidTag($tag, $rendererStates);
      }

      $tagId = $user['userid'];
      $tagName = $user['name'];
      $tagRegistration = $user['registration'];
      $tagGender = $user['gender'];
      $tagEditCount = $user['editcount'];
    }

    if (isset($tagRegistration)) {
      $tagRegistration = date('m/d/Y', strtotime($tagRegistration));
    }

    $view = $formatter->getView();

    if ($view) {
      $template = $view->createTemplateObject('csiXF_bbCode_ADFBB123_tag_wiki_user',
        array(
          'id' => $tagId,
          'name' => $tagName,
          'registration' => $tagRegistration,
          'gender' => $tagGender,
          'editcount' => $tagEditCount,
        ));

      $tagContent = $template->render();
      return trim($tagContent);
    }

    return $tagContent;
  }
}
