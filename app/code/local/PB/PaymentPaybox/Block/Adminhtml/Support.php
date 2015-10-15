<?php

class PB_PaymentPaybox_Block_Adminhtml_Support
    extends Mage_Adminhtml_Block_Abstract
    implements Varien_Data_Form_Element_Renderer_Interface
{
    const PLATFORM_CE = 'ce';
    const PLATFORM_PE = 'pe';
    const PLATFORM_EE = 'ee';
    const PLATFORM_GO = 'go';
    const PLATFORM_UNKNOWN = 'unknown';

    protected static $_platformCode = self::PLATFORM_UNKNOWN;

    /**
     * Render module support tab
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $helper = Mage::helper('pbpaymentpaybox');
        $moduleNameId = 'PB_PaymentPaybox';

        $moduleVersion = $this->_getConfigValue($moduleNameId, 'version');
        $moduleName = $this->_getConfigValue($moduleNameId, 'name');
        $moduleShortDescription = $this->_getConfigValue($moduleNameId, 'descr');
        $moduleLicense = $this->_getConfigValue($moduleNameId, 'license');

        $linkParameters = '';
        $moduleLink = $this->_getConfigValue($moduleNameId, 'permanentlink') . $linkParameters;
        $moduleLicenseLink = $this->_getConfigValue($moduleNameId, 'licenselink') . $linkParameters;
        $moduleSupportLink = $this->_getConfigValue($moduleNameId, 'issueslink') . $linkParameters;

        $logoLink = 'https://paybox.kz/assets/frontend/img/logo.png';

        $html =
            '<style>
                .line {border-top: 1px solid #c6c6c6; padding-top: 10px;}
                .developer-label {color: #000000; font-weight:bold; width: 150px;}
                .developer-text { padding-bottom: 15px;}
                .developer {width: 600px; }
            </style>';

        $html .= '
            <table cellspacing="0" cellpading="0" class="developer">
                <tr>
                    <td></td>
                    <td><img src="' . $logoLink . '" width="130"><br></td>
                </tr>
                <tr>
                    <td class="developer-label">' . $helper->__('Extension:') . '</td>
                    <td class="developer-text">' . $helper->__('<strong>%s</strong> (version %s)', $moduleName, $moduleVersion) . '</td>
                </tr>
                <tr>
                    <td class="developer-label">' . $helper->__('License:') . '</td>
                    <td class="developer-text">' . $helper->__(
            '<a href="%s" target="_blank">%s</a>',
            $moduleLicenseLink,
            $moduleLicense
        ) . '</td>
                </tr>
                <tr>
                    <td class="developer-label">' . $helper->__('Short Description:') . '</td>
                    <td class="developer-text">' . $moduleShortDescription . '</td>
                </tr>
                <tr>
                    <td class="developer-label">' . $helper->__('Documentation:') . '</td>
                    <td class="developer-text">' . $helper->__('On the <a href="%s" target="_blank">module page on GitHub</a>, you can find module description, settings and answers to the questions.', $moduleLink) . '</td>
                </tr>
                <tr>
                    <td class="developer-label line">' . $helper->__('Support:') . '</td>
                    <td class="developer-text line">' . $helper->__('Extension support is available through <a href="%s" target="_blank">issue tracking system</a> on GitHub. You will have to sign up to open a ticket.<br><br>Please, report all bugs and feature requests that are related to this extension.<br><br>In addition, all questions, comments and suggestions on the module, you can write us at <a href="mailto:support@paybox.kz">support@paybox.kz</a>.',
            $moduleSupportLink) . '</td>
                </tr>
            </table>';


        return $html;
    }

    /**
     * Get config values from provided config file
     *
     * @param $module
     * @param $config
     * @return Mage_Core_Model_Config_Element|SimpleXMLElement[]
     */
    protected function _getConfigValue($module, $config)
    {
        $locale = Mage::app()->getLocale()->getLocaleCode();
        $defaultLocale = 'en_US';
        $mainConfig = Mage::getConfig();
        $moduleConfig = $mainConfig->getNode('modules/' . $module . '/' . $config);

        if ((string)$moduleConfig) {
            return $moduleConfig;
        }

        if ($moduleConfig->$locale) {
            return $moduleConfig->$locale;
        } else {
            return $moduleConfig->$defaultLocale;
        }
    }

    /**
     * Get Magento platform edition code
     *
     * @return string
     */
    protected function _getPlatform()
    {
        if (self::$_platformCode == self::PLATFORM_UNKNOWN) {

            if (property_exists('Mage', '_currentEdition')) {
                switch (Mage::getEdition()) {
                    case Mage::EDITION_COMMUNITY:
                        self::$_platformCode = self::PLATFORM_CE;
                        break;
                    case Mage::EDITION_PROFESSIONAL:
                        self::$_platformCode = self::PLATFORM_PE;
                        break;
                    case Mage::EDITION_ENTERPRISE:
                        self::$_platformCode = self::PLATFORM_EE;
                        break;
                    case Mage::EDITION_GO:
                        self::$_platformCode = self::PLATFORM_GO;
                        break;
                    default:
                        self::$_platformCode = self::PLATFORM_UNKNOWN;
                }
            }

            if (self::$_platformCode == self::PLATFORM_UNKNOWN) {
                $modulesArray = (array)Mage::getConfig()->getNode('modules')->children();
                $isEnterprise = array_key_exists('Enterprise_Enterprise', $modulesArray);

                $isProfessional = false;
                $isGo = false;

                if ($isEnterprise) {
                    self::$_platformCode = self::PLATFORM_EE;
                } elseif ($isProfessional) {
                    self::$_platformCode = self::PLATFORM_PE;
                } elseif ($isGo) {
                    self::$_platformCode = self::PLATFORM_GO;
                } else {
                    self::$_platformCode = self::PLATFORM_CE;
                }
            }
        }
        return self::$_platformCode;
    }

}