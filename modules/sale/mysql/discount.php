<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/general/discount.php");


/**
 * 
 *
 *
 *
 *
 * @return mixed 
 *
 * @static
 * @link http://dev.1c-bitrix.ru/api_help/sale/classes/csalediscount/index.php
 * @author Bitrix
 */
class CSaleDiscount extends CAllSaleDiscount
{
	
	/**
	 * <p>Функция добавляет новую скидку на сумму заказа с параметрами из массива <i> arFields</i>.</p>
	 *
	 *
	 *
	 *
	 * @param array $arFields  Ассоциативный массив параметров скидки, ключами в котором
	 * являются названия параметров скидки, а значениями - значения
	 * параметров.<br><br> Допустимые ключи: <ul> <li> <b>LID</b> - код сайта, к
	 * которому привязана эта скидка;</li> <li> <b>PRICE_FROM</b> - общая стоимость
	 * заказа, начиная с которой предоставляется эта скидка;</li> <li>
	 * <b>PRICE_TO</b> - общая стоимость заказа, до достижения которой
	 * предоставляется эта скидка;</li> <li> <b>CURRENCY</b> - валюта денежных полей
	 * в записи;</li> <li> <b>DISCOUNT_VALUE</b> - величина скидки;</li> <li> <b>DISCOUNT_TYPE</b> -
	 * тип величины скидки (P - величина задана в процентах, V - величина
	 * задана в абсолютной сумме);</li> <li> <b>ACTIVE</b> - флаг (Y/N) активности
	 * скидки;</li> <li> <b>SORT</b> - индекс сортировки (если по сумме заказа
	 * доступно несколько скидок, то берется первая по сортировке)</li> </ul>
	 *
	 *
	 *
	 * @return int <p>Возвращается код добавленной скидки или <i>false</i> в случае ошибки.
	 * </p><br><br>
	 *
	 * @static
	 * @link http://dev.1c-bitrix.ru/api_help/sale/classes/csalediscount/csalediscount__add.php
	 * @author Bitrix
	 */
	public static function Add($arFields)
	{
		global $DB;
		global $USER;

		$arFields1 = array();
		if (isset($USER) && $USER instanceof CUser && 'CUser' == get_class($USER))
		{
			if (!array_key_exists('CREATED_BY', $arFields) || intval($arFields["CREATED_BY"]) <= 0)
				$arFields["CREATED_BY"] = intval($USER->GetID());
			if (!array_key_exists('MODIFIED_BY', $arFields) || intval($arFields["MODIFIED_BY"]) <= 0)
				$arFields["MODIFIED_BY"] = intval($USER->GetID());
		}
		if (array_key_exists('TIMESTAMP_X', $arFields))
			unset($arFields['TIMESTAMP_X']);
		if (array_key_exists('DATE_CREATE', $arFields))
			unset($arFields['DATE_CREATE']);

		$arFields1['TIMESTAMP_X'] = $DB->GetNowFunction();
		$arFields1['DATE_CREATE'] = $DB->GetNowFunction();

		$boolNewVersion = true;
		if (!array_key_exists('CONDITIONS', $arFields) && !array_key_exists('ACTIONS', $arFields))
		{
			$boolConvert = CSaleDiscount::__ConvertOldFormat('ADD', $arFields);
			if (!$boolConvert)
				return false;
			$boolNewVersion = false;
		}

		if (!CSaleDiscount::CheckFields("ADD", $arFields))
			return false;

		if ($boolNewVersion)
		{
			$boolConvert = CSaleDiscount::__SetOldFields('ADD', $arFields);
			if (!$boolConvert)
				return false;
		}

		$arInsert = $DB->PrepareInsert("b_sale_discount", $arFields);

		if (!empty($arFields1))
		{
			$arInsert[0] .= ', '.implode(', ',array_keys($arFields1));
			$arInsert[1] .= ', '.implode(', ',array_values($arFields1));
		}

		$strSql = "INSERT INTO b_sale_discount(".$arInsert[0].") VALUES(".$arInsert[1].")";
		$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);

		$ID = intval($DB->LastID());

		if (0 < $ID)
		{
			if (!empty($arFields['USER_GROUPS']))
			{
				$arValid = array();
				foreach ($arFields['USER_GROUPS'] as &$value)
				{
					$value = intval($value);
					if (0 < $value)
						$arValid[] = $value;
				}
				if (isset($value))
					unset($value);
				$arFields['USER_GROUPS'] = array_unique($arValid);
				if (!empty($arFields['USER_GROUPS']))
				{
					foreach ($arFields['USER_GROUPS'] as &$value)
					{
						$strSql = "INSERT INTO b_sale_discount_group(DISCOUNT_ID, GROUP_ID) VALUES(".$ID.", ".$value.")";
						$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
					}
					if (isset($value))
						unset($value);
				}
			}
		}

		return $ID;
	}

	
	/**
	 * <p>Функция обновляет параметры скидки с кодом ID на параметры из массива arFields </p>
	 *
	 *
	 *
	 *
	 * @param int $ID  Код скидки.
	 *
	 *
	 *
	 * @param array $arFields  Ассоциативный массив новых параметров скидки, ключами в котором
	 * являются названия параметров, а значениями - новые значения.
	 * Допустимо указание не всех ключей, а только тех, значения которых
	 * необходимо изменить.<br> Допустимые ключи:<br><ul> <li> <b>LID</b> - код сайта,
	 * к которому привязана эта скидка;</li> <li> <b>PRICE_FROM</b> - общая стоимость
	 * заказа, начиная с которой предоставляется эта скидка;</li> <li>
	 * <b>PRICE_TO</b> - общая стоимость заказа, до достижения которой
	 * предоставляется эта скидка;</li> <li> <b>CURRENCY</b> - валюта денежных полей
	 * в записи;</li> <li> <b>DISCOUNT_VALUE</b> - величина скидки;</li> <li> <b>DISCOUNT_TYPE</b> -
	 * тип величины скидки (P - величина задана в процентах, V - величина
	 * задана в абсолютной сумме);</li> <li> <b>ACTIVE</b> - флаг (Y/N) активности
	 * скидки;</li> <li> <b>SORT</b> - индекс сортировки (если по сумме заказа
	 * доступно несколько скидок, то берется первая по сортировке)</li> </ul>
	 *
	 *
	 *
	 * @return int <p>Возвращается код измененной скидки или <i>false</i> в случае
	 * ошибки.</p><br><br>
	 *
	 * @static
	 * @link http://dev.1c-bitrix.ru/api_help/sale/classes/csalediscount/csalediscount__update.700e1b34.php
	 * @author Bitrix
	 */
	public static function Update($ID, $arFields)
	{
		global $DB;
		global $USER;

		$ID = intval($ID);
		if (0 >= $ID)
			return false;

		$arFields1 = array();
		if (array_key_exists('CREATED_BY',$arFields))
			unset($arFields['CREATED_BY']);
		if (array_key_exists('DATE_CREATE',$arFields))
			unset($arFields['DATE_CREATE']);
		if (array_key_exists('TIMESTAMP_X', $arFields))
			unset($arFields['TIMESTAMP_X']);
		if (isset($USER) && $USER instanceof CUser && 'CUser' == get_class($USER))
		{
			if (!array_key_exists('MODIFIED_BY', $arFields) || intval($arFields["MODIFIED_BY"]) <= 0)
				$arFields["MODIFIED_BY"] = intval($USER->GetID());
		}
		$arFields1['TIMESTAMP_X'] = $DB->GetNowFunction();

		$boolNewVersion = true;
		if (!array_key_exists('CONDITIONS', $arFields) && !array_key_exists('ACTIONS', $arFields))
		{
			$arFields['ID'] = $ID;
			$boolConvert = CSaleDiscount::__ConvertOldFormat('UPDATE', $arFields);
			if (!$boolConvert)
				return false;
			$boolNewVersion = false;
		}
		if (array_key_exists('ID', $arFields))
			unset($arFields['ID']);

		if (!CSaleDiscount::CheckFields("UPDATE", $arFields))
			return false;

		if ($boolNewVersion)
		{
			$boolConvert = CSaleDiscount::__SetOldFields('UPDATE', $arFields);
			if (!$boolConvert)
				return false;
		}

		$strUpdate = $DB->PrepareUpdate("b_sale_discount", $arFields);
		if (!empty($strUpdate))
		{
			$arAdd = array();
			if (!empty($arFields1))
			{
				foreach ($arFields1 as $key => $value)
				{
					$arAdd[] = $key."=".$value;
				}
				$strUpdate .= ', '.implode(', ', $arAdd);
			}

			$strSql = "UPDATE b_sale_discount SET ".$strUpdate." WHERE ID = ".$ID;
			$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		}

		if (array_key_exists('USER_GROUPS',$arFields) && is_array($arFields['USER_GROUPS']) && !empty($arFields['USER_GROUPS']))
		{
			$DB->Query("DELETE FROM b_sale_discount_group WHERE DISCOUNT_ID = ".$ID, false, "File: ".__FILE__."<br>Line: ".__LINE__);
			$arValid = array();
			foreach ($arFields['USER_GROUPS'] as &$value)
			{
				$value = intval($value);
				if (0 < $value)
					$arValid[] = $value;
			}
			if (isset($value))
				unset($value);
			$arFields['USER_GROUPS'] = array_unique($arValid);
			if (!empty($arFields['USER_GROUPS']))
			{
				foreach ($arFields['USER_GROUPS'] as &$value)
				{
					$strSql = "INSERT INTO b_sale_discount_group(DISCOUNT_ID, GROUP_ID) VALUES(".$ID.", ".$value.")";
					$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
				}
				if (isset($value))
					unset($value);
			}
		}

		return $ID;
	}

	
	/**
	 * <p>Функция удаляет скидку с кодом ID </p>
	 *
	 *
	 *
	 *
	 * @param int $ID  Код скидки.
	 *
	 *
	 *
	 * @return bool <p>Возвращается <i>true</i> в случае успешного удаления и <i>false</i> - в
	 * противном случае.</p><br><br>
	 *
	 * @static
	 * @link http://dev.1c-bitrix.ru/api_help/sale/classes/csalediscount/csalediscount__delete.7216613a.php
	 * @author Bitrix
	 */
	public static function Delete($ID)
	{
		global $DB;
		$ID = intval($ID);
		if (0 >= $ID)
			return false;

		$DB->Query("DELETE FROM b_sale_discount_group WHERE DISCOUNT_ID = ".$ID, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		return $DB->Query("DELETE FROM b_sale_discount WHERE ID = ".$ID, true);
	}

	
	/**
	 * <p>Функция возвращает результат выборки записей из скидок на заказ в соответствии со своими параметрами. </p>
	 *
	 *
	 *
	 *
	 * @param array $arOrder = array() Массив, в соответствии с которым сортируются результирующие
	 * записи. Массив имеет вид: <pre class="syntax">array( "название_поля1" =&gt;
	 * "направление_сортировки1", "название_поля2" =&gt;
	 * "направление_сортировки2", . . . )</pre> В качестве "название_поля<i>N</i>"
	 * может стоять любое поле корзины, а в качестве
	 * "направление_сортировки<i>X</i>" могут быть значения "<i>ASC</i>" (по
	 * возрастанию) и "<i>DESC</i>" (по убыванию).<br><br> Если массив сортировки
	 * имеет несколько элементов, то результирующий набор сортируется
	 * последовательно по каждому элементу (т.е. сначала сортируется по
	 * первому элементу, потом результат сортируется по второму и
	 * т.д.). <br><br> Значение по умолчанию - пустой массив array() - означает,
	 * что результат отсортирован не будет.
	 *
	 *
	 *
	 * @param array $arFilter = array() Массив, в соответствии с которым фильтруются записи скидки.
	 * Массив имеет вид: <pre class="syntax">array(
	 * "[модификатор1][оператор1]название_поля1" =&gt; "значение1",
	 * "[модификатор2][оператор2]название_поля2" =&gt; "значение2", . . . )</pre>
	 * Удовлетворяющие фильтру записи возвращаются в результате, а
	 * записи, которые не удовлетворяют условиям фильтра,
	 * отбрасываются.<br><br> Допустимыми являются следующие модификаторы:
	 * <ul> <li> <b> !</b> - отрицание;</li> <li> <b> +</b> - значения null, 0 и пустая строка
	 * так же удовлетворяют условиям фильтра.</li> </ul> Допустимыми
	 * являются следующие операторы: <ul> <li> <b>&gt;=</b> - значение поля больше
	 * или равно передаваемой в фильтр величины;</li> <li> <b>&gt;</b> - значение
	 * поля строго больше передаваемой в фильтр величины;</li> <li><b> -
	 * значение поля меньше или равно передаваемой в фильтр
	 * величины;</b></li> <li><b> - значение поля строго меньше передаваемой в
	 * фильтр величины;</b></li> <li> <b>@</b> - значение поля находится в
	 * передаваемом в фильтр разделенном запятой списке значений;</li> <li>
	 * <b>~</b> - значение поля проверяется на соответствие передаваемому в
	 * фильтр шаблону;</li> <li> <b>%</b> - значение поля проверяется на
	 * соответствие передаваемой в фильтр строке в соответствии с
	 * языком запросов.</li> </ul> В качестве "название_поляX" может стоять
	 * любое поле корзины.<br><br> Пример фильтра: <pre class="syntax">array("!CURRENCY" =&gt;
	 * "USD")</pre> Этот фильтр означает "выбрать все записи, в которых
	 * значение в поле CURRENCY (валюта) не равно USD".<br><br> Значение по
	 * умолчанию - пустой массив array() - означает, что результат
	 * отфильтрован не будет.
	 *
	 *
	 *
	 * @param array $arGroupBy = false Массив полей, по которым группируются записи скидок. Массив имеет
	 * вид: <pre class="syntax">array("название_поля1", "группирующая_функция2" =&gt;
	 * "название_поля2", ...)</pre> В качестве "название_поля<i>N</i>" может стоять
	 * любое поле служб доставки. В качестве группирующей функции могут
	 * стоять: <ul> <li> <b> COUNT</b> - подсчет количества;</li> <li> <b>AVG</b> - вычисление
	 * среднего значения;</li> <li> <b>MIN</b> - вычисление минимального
	 * значения;</li> <li> <b> MAX</b> - вычисление максимального значения;</li> <li>
	 * <b>SUM</b> - вычисление суммы.</li> </ul> Если массив пустой, то функция
	 * вернет число записей, удовлетворяющих фильтру.<br><br> Значение по
	 * умолчанию - <i>false</i> - означает, что результат группироваться не
	 * будет.
	 *
	 *
	 *
	 * @param array $arNavStartParams = false Массив параметров выборки. Может содержать следующие ключи: <ul>
	 * <li>"<b>nTopCount</b>" - количество возвращаемых функцией записей будет
	 * ограничено сверху значением этого ключа;</li> <li> любой ключ,
	 * принимаемый методом <b> CDBResult::NavQuery</b> в качестве третьего
	 * параметра.</li> </ul> Значение по умолчанию - <i>false</i> - означает, что
	 * параметров выборки нет.
	 *
	 *
	 *
	 * @param array $arSelectFields = array() Массив полей записей, которые будут возвращены функцией. Можно
	 * указать только те поля, которые необходимы. Если в массиве
	 * присутствует значение "*", то будут возвращены все доступные
	 * поля.<br><br> Значение по умолчанию - пустой массив array() - означает,
	 * что будут возвращены все поля основной таблицы запроса.
	 *
	 *
	 *
	 * @return CDBResult <p>Возвращается объект класса CDBResult, содержащий набор
	 * ассоциативных массивов с ключами:</p><table class="tnormal" width="100%"> <tr> <th
	 * width="15%">Ключ</th> <th>Описание</th> </tr> <tr> <td>ID</td> <td>Код скидки.</td> </tr> <tr>
	 * <td>LID</td> <td>Код сайта, к которому привязана эта скидка.</td> </tr> <tr>
	 * <td>PRICE_FROM</td> <td>Общая стоимость заказа, начиная с которой
	 * предоставляется эта скидка.</td> </tr> <tr> <td>PRICE_TO</td> <td>Общая стоимость
	 * заказа, до достижения которой предоставляется эта скидка.</td> </tr>
	 * <tr> <td>CURRENCY</td> <td>Валюта денежных полей в записи.</td> </tr> <tr>
	 * <td>DISCOUNT_VALUE</td> <td>Величина скидки.</td> </tr> <tr> <td>DISCOUNT_TYPE</td> <td>Тип
	 * величины скидки (P - величина задана в процентах, V - величина
	 * задана в абсолютной сумме).</td> </tr> <tr> <td>ACTIVE</td> <td>Флаг (Y/N)
	 * активности скидки.</td> </tr> <tr> <td>SORT</td> <td>Индекс сортировки (если по
	 * сумме заказа доступно несколько скидок, то берется первая по
	 * сортировке).</td> </tr> <tr> <td>USER_GROUPS</td> <td>Перечень групп пользователей,
	 * на которые должна действовать скидка.</td> </tr> </table><p>Если в качестве
	 * параметра arGroupBy передается пустой массив, то функция вернет число
	 * записей, удовлетворяющих фильтру.</p><a name="examples"></a>
	 *
	 *
	 * <h4>Example</h4> 
	 * <pre>
	 * &lt;?
	 * // Выберем величину активной скидки для текущего сайта и стоимости 
	 * // заказа $ORDER_PRICE (в базовой валюте этого сайта)
	 * $db_res = CSaleDiscount::GetList(
	 *         array("SORT" =&gt; "ASC"),
	 *         array(
	 *               "LID" =&gt; SITE_ID, 
	 *               "ACTIVE" =&gt; "Y", 
	 *               "&gt;=PRICE_TO" =&gt; $ORDER_PRICE, 
	 *               "&lt;=PRICE_FROM" =&gt; $ORDER_PRICE
	 *             ),
	 *         false,
	 *         false,
	 *         array()
	 *     );
	 * if ($ar_res = $db_res-&gt;Fetch())
	 * {
	 *    echo "Наша скидка - ";
	 *    if ($ar_res["DISCOUNT_TYPE"] == "P")
	 *    {
	 *       echo $ar_res["DISCOUNT_VALUE"]."%";
	 *    }
	 *    else
	 *    {
	 *       echo CurrencyFormat($ar_res["DISCOUNT_VALUE"], $ar_res["CURRENCY"]);
	 *    }
	 * }
	 * ?&gt;
	 * </pre>
	 *
	 *
	 * @static
	 * @link http://dev.1c-bitrix.ru/api_help/sale/classes/csalediscount/csalediscount__getlist.7e987f7e.php
	 * @author Bitrix
	 */
	public static function GetList($arOrder = array(), $arFilter = array(), $arGroupBy = false, $arNavStartParams = false, $arSelectFields = array())
	{
		global $DB;

		if (!is_array($arOrder) && !is_array($arFilter))
		{
			$arOrder = strval($arOrder);
			$arFilter = strval($arFilter);
			if ('' != $arOrder && '' != $arFilter)
				$arOrder = array($arOrder => $arFilter);
			else
				$arOrder = array();
			if (is_array($arGroupBy))
				$arFilter = $arGroupBy;
			else
				$arFilter = array();
			if (array_key_exists("PRICE", $arFilter))
			{
				$valTmp = $arFilter["PRICE"];
				unset($arFilter["PRICE"]);
				$arFilter["<=PRICE_FROM"] = $valTmp;
				$arFilter[">=PRICE_TO"] = $valTmp;
			}
			$arGroupBy = false;
		}

		$arFields = array(
			"ID" => array("FIELD" => "D.ID", "TYPE" => "int"),
			"XML_ID" => array("FIELD" => "D.XML_ID", "TYPE" => "string"),
			"LID" => array("FIELD" => "D.LID", "TYPE" => "string"),
			"SITE_ID" => array("FIELD" => "D.LID", "TYPE" => "string"),
			"NAME" => array("FIELD" => "D.NAME", "TYPE" => "string"),
			"PRICE_FROM" => array("FIELD" => "D.PRICE_FROM", "TYPE" => "double", "WHERE" => array("CSaleDiscount", "PrepareCurrency4Where")),
			"PRICE_TO" => array("FIELD" => "D.PRICE_TO", "TYPE" => "double", "WHERE" => array("CSaleDiscount", "PrepareCurrency4Where")),
			"CURRENCY" => array("FIELD" => "D.CURRENCY", "TYPE" => "string"),
			"DISCOUNT_VALUE" => array("FIELD" => "D.DISCOUNT_VALUE", "TYPE" => "double"),
			"DISCOUNT_TYPE" => array("FIELD" => "D.DISCOUNT_TYPE", "TYPE" => "char"),
			"ACTIVE" => array("FIELD" => "D.ACTIVE", "TYPE" => "char"),
			"SORT" => array("FIELD" => "D.SORT", "TYPE" => "int"),
			"ACTIVE_FROM" => array("FIELD" => "D.ACTIVE_FROM", "TYPE" => "datetime"),
			"ACTIVE_TO" => array("FIELD" => "D.ACTIVE_TO", "TYPE" => "datetime"),
			"TIMESTAMP_X" => array("FIELD" => "D.TIMESTAMP_X", "TYPE" => "datetime"),
			"MODIFIED_BY" => array("FIELD" => "D.MODIFIED_BY", "TYPE" => "int"),
			"DATE_CREATE" => array("FIELD" => "D.DATE_CREATE", "TYPE" => "datetime"),
			"CREATED_BY" => array("FIELD" => "D.CREATED_BY", "TYPE" => "int"),
			"PRIORITY" => array("FIELD" => "D.PRIORITY", "TYPE" => "int"),
			"LAST_DISCOUNT" => array("FIELD" => "D.LAST_DISCOUNT", "TYPE" => "char"),
			"VERSION" => array("FIELD" => "D.VERSION", "TYPE" => "int"),
			"CONDITIONS" => array("FIELD" => "D.CONDITIONS", "TYPE" => "string"),
			"UNPACK" => array("FIELD" => "D.UNPACK", "TYPE" => "string"),
			"APPLICATION" => array("FIELD" => "D.APPLICATION", "TYPE" => "string"),
			"ACTIONS" => array("FIELD" => "D.ACTIONS", "TYPE" => "string"),
			"USER_GROUPS" => array("FIELD" => "DG.GROUP_ID", "TYPE" => "int","FROM" => "LEFT JOIN b_sale_discount_group DG ON (D.ID = DG.DISCOUNT_ID)")
		);

		if (empty($arSelectFields))
			$arSelectFields = array('ID','LID','SITE_ID','PRICE_FROM','PRICE_TO','CURRENCY','DISCOUNT_VALUE','DISCOUNT_TYPE','ACTIVE','SORT','ACTIVE_FROM','ACTIVE_TO','PRIORITY','LAST_DISCOUNT','VERSION','NAME');
		elseif (is_array($arSelectFields) && in_array('*',$arSelectFields))
			$arSelectFields = array('ID','LID','SITE_ID','PRICE_FROM','PRICE_TO','CURRENCY','DISCOUNT_VALUE','DISCOUNT_TYPE','ACTIVE','SORT','ACTIVE_FROM','ACTIVE_TO','PRIORITY','LAST_DISCOUNT','VERSION','NAME');

		$arSqls = CSaleOrder::PrepareSql($arFields, $arOrder, $arFilter, $arGroupBy, $arSelectFields);

		$arSqls["SELECT"] = str_replace("%%_DISTINCT_%%", '', $arSqls["SELECT"]);

		if (empty($arGroupBy) && is_array($arGroupBy))
		{
			$strSql = "SELECT ".$arSqls["SELECT"]." FROM b_sale_discount D ".$arSqls["FROM"];
			if (!empty($arSqls["WHERE"]))
				$strSql .= " WHERE ".$arSqls["WHERE"];
			if (!empty($arSqls["GROUPBY"]))
				$strSql .= " GROUP BY ".$arSqls["GROUPBY"];

			$dbRes = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
			if ($arRes = $dbRes->Fetch())
				return $arRes["CNT"];
			else
				return false;
		}

		$strSql = "SELECT ".$arSqls["SELECT"]." FROM b_sale_discount D ".$arSqls["FROM"];
		if (!empty($arSqls["WHERE"]))
			$strSql .= " WHERE ".$arSqls["WHERE"];
		if (!empty($arSqls["GROUPBY"]))
			$strSql .= " GROUP BY ".$arSqls["GROUPBY"];
		if (!empty($arSqls["ORDERBY"]))
			$strSql .= " ORDER BY ".$arSqls["ORDERBY"];

		$intTopCount = 0;
		$boolNavStartParams = (!empty($arNavStartParams) && is_array($arNavStartParams));
		if ($boolNavStartParams && array_key_exists('nTopCount', $arNavStartParams))
		{
			$intTopCount = intval($arNavStartParams["nTopCount"]);
		}
		if ($boolNavStartParams && 0 >= $intTopCount)
		{
			$strSql_tmp = "SELECT COUNT('x') as CNT FROM b_sale_discount D ".$arSqls["FROM"];
			if (!empty($arSqls["WHERE"]))
				$strSql_tmp .= " WHERE ".$arSqls["WHERE"];
			if (!empty($arSqls["GROUPBY"]))
				$strSql_tmp .= " GROUP BY ".$arSqls["GROUPBY"];

			$dbRes = $DB->Query($strSql_tmp, false, "File: ".__FILE__."<br>Line: ".__LINE__);
			$cnt = 0;
			if (empty($arSqls["GROUPBY"]))
			{
				if ($arRes = $dbRes->Fetch())
					$cnt = $arRes["CNT"];
			}
			else
			{
				$cnt = $dbRes->SelectedRowsCount();
			}

			$dbRes = new CDBResult();

			$dbRes->NavQuery($strSql, $cnt, $arNavStartParams);
		}
		else
		{
			if ($boolNavStartParams && 0 < $intTopCount)
			{
				$strSql .= " LIMIT ".$intTopCount;
			}
			$dbRes = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		}

		return $dbRes;
	}

	public static function GetDiscountGroupList($arOrder = array(), $arFilter = array(), $arGroupBy = false, $arNavStartParams = false, $arSelectFields = array())
	{
		global $DB;

		$arFields = array(
			"ID" => array("FIELD" => "DG.ID", "TYPE" => "int"),
			"DISCOUNT_ID" => array("FIELD" => "DG.DISCOUNT_ID", "TYPE" => "int"),
			"GROUP_ID" => array("FIELD" => "DG.GROUP_ID", "TYPE" => "int"),
		);

		$arSqls = CSaleOrder::PrepareSql($arFields, $arOrder, $arFilter, $arGroupBy, $arSelectFields);

		$arSqls["SELECT"] = str_replace("%%_DISTINCT_%%", "", $arSqls["SELECT"]);

		if (empty($arGroupBy) && is_array($arGroupBy))
		{
			$strSql = "SELECT ".$arSqls["SELECT"]." FROM b_sale_discount_group DG ".$arSqls["FROM"];
			if (!empty($arSqls["WHERE"]))
				$strSql .= " WHERE ".$arSqls["WHERE"];
			if (!empty($arSqls["GROUPBY"]))
				$strSql .= " GROUP BY ".$arSqls["GROUPBY"];

			$dbRes = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
			if ($arRes = $dbRes->Fetch())
				return $arRes["CNT"];
			else
				return false;
		}

		$strSql = "SELECT ".$arSqls["SELECT"]." FROM b_sale_discount_group DG ".$arSqls["FROM"];
		if (!empty($arSqls["WHERE"]))
			$strSql .= " WHERE ".$arSqls["WHERE"];
		if (!empty($arSqls["GROUPBY"]))
			$strSql .= " GROUP BY ".$arSqls["GROUPBY"];
		if (!empty($arSqls["ORDERBY"]))
			$strSql .= " ORDER BY ".$arSqls["ORDERBY"];

		$intTopCount = 0;
		$boolNavStartParams = (!empty($arNavStartParams) && is_array($arNavStartParams));
		if ($boolNavStartParams && array_key_exists('nTopCount', $arNavStartParams))
		{
			$intTopCount = intval($arNavStartParams["nTopCount"]);
		}
		if ($boolNavStartParams && 0 >= $intTopCount)
		{
			$strSql_tmp = "SELECT COUNT('x') as CNT FROM b_sale_discount_group DG ".$arSqls["FROM"];
			if (!empty($arSqls["WHERE"]))
				$strSql_tmp .= " WHERE ".$arSqls["WHERE"];
			if (!empty($arSqls["GROUPBY"]))
				$strSql_tmp .= " GROUP BY ".$arSqls["GROUPBY"];

			$dbRes = $DB->Query($strSql_tmp, false, "File: ".__FILE__."<br>Line: ".__LINE__);
			$cnt = 0;
			if (empty($arSqls["GROUPBY"]))
			{
				if ($arRes = $dbRes->Fetch())
					$cnt = $arRes["CNT"];
			}
			else
			{
				$cnt = $dbRes->SelectedRowsCount();
			}

			$dbRes = new CDBResult();

			$dbRes->NavQuery($strSql, $cnt, $arNavStartParams);
		}
		else
		{
			if ($boolNavStartParams && 0 < $intTopCount)
			{
				$strSql .= " LIMIT ".$intTopCount;
			}
			$dbRes = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		}

		return $dbRes;
	}
}
?>