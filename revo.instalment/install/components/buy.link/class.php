<?
if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();
use Bitrix\Main\Config\Option;

class CRevoBuyLink extends \CBitrixComponent
{
    /**
     * Подключает языковые файлы
     */
    public function onIncludeComponentLang()
    {
        $this->includeComponentLang(basename(__FILE__));
        \Bitrix\Main\Localization\Loc::loadMessages(__FILE__);
    }

    /**
     * Подготавливает входные параметры
     * @param $params
     * @return array
     */
    public function onPrepareComponentParams($params)
    {
        return $params;
    }

    /**
     * Проверяет подключение необходимых модулей
     * @throws Main\LoaderException
     */
    protected function checkModules()
    {
        \Bitrix\Main\Loader::includeModule('revo.instalment');
    }

    /**
     * Выполняет логику работы компонента
     */
    public function executeComponent()
    {
        try
        {
            $this->checkModules();
            $this->executeProlog();

            $this->getResult();

            $this->executeEpilog();
        }
        catch (Main\LoaderException $e)
        {
            ShowError($e->getMessage());
        }
    }

    /**
     * Выполяет действия перед кешированием
     */
    protected function executeProlog()
    {

    }

    protected function getResult() {
        global $USER;

        if (!$this->arParams['PRICE']) {
            \Bitrix\Main\Loader::includeModule('catalog');

            $this->arParams['PRICE'] = \CPrice::GetBasePrice($this->arParams['PRODUCT_ID'])['PRICE'];
        }

        $showBlock = Option::get('revo.instalment', 'debug_mode', 'Y') != 'Y' || $USER->IsAdmin();
        $minPrice = Option::get('revo.instalment', 'detail_min_price', 0);
		$maxPrice = Option::get('revo.instalment', 'detail_max_price', 0);

        if ($showBlock && $this->arParams['PRICE'] >= $minPrice && ($maxPrice && $this->arParams['PRICE'] <= $maxPrice))
            $this->includeComponentTemplate();
    }

    /**
     * Выполняет действия после выполения компонента
     */
    protected function executeEpilog()
    {

    }

    /**
     * Кешируемые ключи arResult
     * @var array()
     */
    protected $cacheKeys = array();

    /**
     * Дополнительные параметры, от которых должен зависеть кеш
     * @var array
     */
    protected $cacheAddon = array();

    /**
     * Определяет читать данные из кеша или нет
     * @return bool
     */
    protected function readDataFromCache()
    {
        return !($this->startResultCache(false, $this->cacheAddon));
    }

    /**
     * Кеширует ключи массива arResult
     */
    protected function putDataToCache()
    {
        if (is_array($this->cacheKeys) && sizeof($this->cacheKeys) > 0)
        {
            $this->setResultCacheKeys($this->cacheKeys);
        }
    }

    /**
     * Прерывает кеширование
     */
    protected function abortDataCache()
    {
        $this->abortResultCache();
    }
}