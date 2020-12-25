<?php

//检查是否为法定节假日
namespace Date;
//注意这里要跟composer.json下的psr-4下的路径一致，

/**
 * Class Date
 *
 * @author wch 20201224
 * @package Date
 */
class Date
{
	/**
	 * 获取一天的节假日情况
	 *
	 * @author wch 20201224
	 * @param $day
	 * @return array
	 */
	public function getDay($day, $json = false, $info = false): array
	{
		$check = $this->check($day);
		if ($check['status'] == 0)
			return $check;//检查日期合法性
		$year = substr($day, 0, 4);
		$returnData = '';
		$yearData = $this->yeardate($year, $json, $info);

		if ($yearData) {
			$new_day = substr($day, 4, 4);
			$dayValue = @$yearData[$new_day];
			if (!empty($dayValue) || is_numeric($dayValue))
				$returnData = $dayValue;
		}
		if (!is_numeric($returnData))
			$returnData = $this->checkwork($day);
		$return = array(
			'status' => 1,
			'info' => $returnData
		);
		return $return;
	}

	/**
	 * 检查是否是周未
	 *
	 * @author wch 20201224
	 * @param $data
	 * @return int
	 */
	protected function checkwork($data)
	{
		$weak = date("N", strtotime($data));
		return in_array($weak, array(
			6,
			7
		)) ? 1 : 0;
	}

	/**
	 * 检查是否为合法日期
	 *
	 * @author wch 20201224
	 * @param $day
	 * @return array
	 */
	protected function check($day): array
	{
		$return = array(
			'status' => 0,
			'info' => ''
		);
		if (empty($day) || $day < 4) {
			$return['info'] = '日期参数不正确';
			return $return;
		}
		$day_time = strtotime($day);
		if (date('Ymd', $day_time) != $day) {
			$return['info'] = '日期格式错误';
			return $return;
		}
		$return['status'] = 1;
		return $return;
	}

	/**
	 * 获取一年的数据
	 *
	 * @author wch 20201224
	 * @param string $year
	 * @return mixed
	 */
	private function yearDate($year = '', $json = false, $info = false)
	{
		if (empty($year))
			$year = date('Y');
		$new_file = 'http://tool.bitefu.net/jiari/?d=' . $year . ($json ? '&back=json' : '') . ($info ? '&info=1' : '');
		$nowData = cache('nowData' . $year);
		if (!$nowData) {
			$nowData = json_decode(file_get_contents($new_file), true);
			if (!$nowData) {
				$new_file = dirname(__DIR__) . '/data/' . $year . '_data.json';
				if (file_exists($new_file)) {
					$nowData[$year] = json_decode(file_get_contents($new_file), true);
				}
			}
			cache('nowData' . $year, $nowData);
		}

		return $nowData[$year];
	}
}