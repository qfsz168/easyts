<?php
/**
 * Created by PhpStorm.
 * User: 王崇全
 * Date: 2017/12/14
 * Time: 16:34
 */

namespace app\common\model;


use think\Db;
use think\Model;

class Group extends Model
{

	const TYPE = [
		'article',
		'config',
		'user',
		'adminer',
	]; //分组类型

	//自动时间
	protected $autoWriteTimestamp = "datetime";
	protected $updateTime         = false;

	protected function initialize()
	{
		parent::initialize();
	}

	protected function getIsLeafNodeAttr($value, $data)
	{
		return $data["ln"] - $data["rn"] == 1 ? true : false;
	}

	protected function getChildAttr($value, $data)
	{

		return $this->GetList($data["type"], $data["id"]);
	}

	/**
	 * setTypeAttr
	 * @author 王崇全
	 * @date
	 * @param $value
	 * @param $data
	 * @return mixed
	 * @throws \Exception
	 */
	protected function setTypeAttr($value, $data)
	{
		if (!in_array($value, self::TYPE))
		{
			throw new \Exception("分组类型错误");
		}

		return $value;
	}

	/**
	 * 添加分组
	 * @author 王崇全
	 * @date
	 * @param string $type  分类
	 * @param string $title 组名
	 * @param string $pgid  父组ID
	 * @return string
	 * @throws \Exception
	 */
	public function Add(string $type, string $title, string $pgid = "")
	{
		if (!$pgid)
		{
			$pgrn = $this->max("rn") + 1;
			if (!$pgrn)
			{
				$pgrn = 2;
			}
		}
		else
		{
			$pgrn = $this->where(["id" => $pgid])
				->value("rn");
		}
		if (!$pgrn)
		{
			throw new \Exception("此父组不存在");
		}

		$newid = uuid();

		Db::startTrans();
		try
		{
			$this->where("ln", ">=", $pgrn)
				->setInc("ln", 2);
			$this->where("rn", ">=", $pgrn)
				->setInc("rn", 2);

			$new = $this->insert([
				"type"  => $type,
				"ln"    => $pgrn,
				"rn"    => $pgrn + 1,
				"pid"   => $pgid ?? "",
				"title" => $title ?? "",
				"id"    => $newid,
			]);
			if (!$new)
			{
				throw new \Exception("分组添加失败");
			}

			Db::commit();
		}
		catch (\Exception $e)
		{
			Db::rollback();
			throw new \Exception($e->getMessage());
		}

		return $newid;
	}

	/**
	 * 编辑分组
	 * @author 王崇全
	 * @date
	 * @param string      $gid   分组ID
	 * @param string|null $title 分组名称
	 * @param string      $pgid  父组ID
	 * @return false|int
	 * @throws \Exception
	 */
	public function Edit(string $gid, string $title = null, string $pgid = "")
	{
		$data = [];
		if (isset($title))
		{
			$data["title"] = $title;
		}

		Db::startTrans();
		try
		{
			if (isset($pgid))
			{
				if ($pgid == $gid)
				{
					throw new \Exception("父组不能是自己");
				}

				$this->ChangeParentGroup($gid, $pgid);
			}
			$rows = $this->isUpdate()
				->save($data, ["id" => $gid]);
		}
		catch (\Exception $e)
		{
			Db::rollback();
			throw new \Exception($e->getMessage());
		}

		Db::commit();

		return $rows;
	}

	/**
	 * 删除分组
	 * @author 王崇全
	 * @date
	 * @param string $gid 组ID
	 * @return int
	 * @throws \Exception
	 */
	public function Del(string $gid)
	{
		Db::startTrans();
		try
		{
			$info = $this->field([
				"ln" => "ln",
				"rn" => "rn",
				"rn-ln+1 AS width",
			])
				->where("id", $gid)
				->find();
			if (!$info)
			{
				throw new \Exception("此分组不存在");
			}

			$lcode = $info->getAttr("ln");
			$rcode = $info->getAttr("rn");
			$width = $info->getAttr("width");

			$rows = $this->where([
				"ln" => [
					"between",
					[
						$lcode,
						$rcode,
					],
				],
			])
				->delete();

			$this->where("ln", ">", $lcode)
				->setDec("ln", $width);
			$this->where("rn", ">", $rcode)
				->setDec("rn", $width);

			Db::commit();
		}
		catch (\Exception $e)
		{
			Db::rollback();
			throw new \Exception($e->getMessage());
		}

		return $rows;
	}

	/**
	 * 平移分组
	 * @author 王崇全
	 * @date
	 * @param string $gid 分组ID
	 * @param string $pos 目标位置的分组ID
	 * @return void
	 * @throws \Exception
	 */
	public function MoveTo(string $gid, string $pos)
	{
		if ($gid == $pos)
		{
			return;
		}

		Db::startTrans();

		$info = $this->GetNodeInfo($gid);
		if (!$info)
		{
			throw new \Exception("此分组不存在");
		}

		$infoBeforeGroup = $this->GetNodeInfo($pos);
		if (!$infoBeforeGroup)
		{
			throw new \Exception("目标位置分组不存在");
		}

		if ($info["pid"] != $infoBeforeGroup["pid"])
		{
			throw new \Exception("只能平级移动");
		}

		$distance = $info["ln"] - $infoBeforeGroup["ln"];

		//是否是向左移动
		$toLeft = $distance > 0 ? true : false;

		//获取此节点的所有子节点
		$groups = $this->where([
			"ln" => [
				"between",
				[
					$info["ln"],
					$info["rn"],
				],
			],
		])
			->column("id");
		try
		{
			//移动分组
			if ($toLeft)
			{
				//s1 顺移其他分组
				$this->where("ln", ">=", $infoBeforeGroup["ln"])
					->where("ln", "<", $info["ln"])
					->setInc("ln", $info["width"]);

				$this->where("rn", ">", $infoBeforeGroup["ln"])
					->where("rn", "<", $info["ln"])
					->setInc("rn", $info["width"]);

				//s2 移动本分组
				$this->where([
					"id" => [
						"IN",
						$groups,
					],
				])
					->setDec("ln", $distance);

				$this->where([
					"id" => [
						"IN",
						$groups,
					],
				])
					->setDec("rn", $distance);
			}
			else
			{
				$distance = $infoBeforeGroup["rn"] - $info["rn"];

				//s1 顺移其他分组
				$this->where("ln", "<", $infoBeforeGroup["rn"])
					->where("ln", ">", $info["rn"])
					->setDec("ln", $info["width"]);

				$this->where("rn", ">", $info["rn"])
					->where("rn", "<=", $infoBeforeGroup["rn"])
					->setDec("rn", $info["width"]);

				//s2 移动本分组
				$this->where([
					"id" => [
						"IN",
						$groups,
					],
				])
					->setInc("ln", $distance);

				$this->where([
					"id" => [
						"IN",
						$groups,
					],
				])
					->setInc("rn", $distance);
			}

			Db::commit();
		}
		catch (\Exception $e)
		{
			Db::rollback();
			throw new \Exception($e->getMessage());
		}
	}

	/**
	 * 修改父分组
	 * @author 王崇全
	 * @date
	 * @param string $gid
	 * @param string $pgid
	 * @return void
	 * @throws \Exception
	 * @throws \think\db\exception\DataNotFoundException
	 * @throws \think\db\exception\ModelNotFoundException
	 * @throws \think\exception\DbException
	 */
	protected function ChangeParentGroup(string $gid, string $pgid)
	{

		//父节点不能是自己
		if ($gid == $pgid)
		{
			throw new \Exception("父节点不能是自己");
		}

		Db::startTrans();

		//节点信息
		$info = $this->GetNodeInfo($gid);
		if (!$info)
		{
			throw new \Exception("节点不存在");
		}

		//父节点没有改变
		if ($info["pid"] == $pgid)
		{
			Db::commit();

			return;
		}

		//获取此节点的所有子节点
		$groups = $this->where([
			"ln" => [
				"between",
				[
					$info["ln"],
					$info["rn"],
				],
			],
		])
			->column("id");

		$pgid = trim($pgid);
		if ($pgid === "" || $pgid === '""' || $pgid === "''")
		{ // 父节点为根节点
			$maxCode = $this->max("rn");
			$d       = $maxCode - $info["rn"];

			try
			{
				//s1 节点右侧的所有左右值左移
				$this->where("ln", ">=", $info["rn"] + 1)
					->setDec("ln", $info["width"]);
				$this->where("rn", ">=", $info["rn"] + 1)
					->setDec("rn", $info["width"]);

				//s2 节点右移
				$this->where([
					"id" => [
						"IN",
						$groups,
					],
				])
					->setInc("ln", $d);
				$this->where([
					"id" => [
						"IN",
						$groups,
					],
				])
					->setInc("rn", $d);

				//s3 修改自身的父组ID
				$this->isUpdate()
					->save(["pid" => ""], ["id" => $gid]);

			}
			catch (\Exception $e)
			{
				Db::rollback();
				throw new \Exception($e->getMessage());
			}

		}
		else
		{ // 父节点不是根节点

			//父节点信息
			$pInfo = $this->field([
				"pid" => "pid",
				"ln"  => "ln",
				"rn"  => "rn",
				"rn - ln + 1 AS width",
			])
				->where(["id" => $pgid])
				->find();
			if (!$pInfo)
			{
				throw new \Exception("父节点不存在");
			}

			//父节点不能是自己的子节点
			if ($pInfo["ln"] > $info["ln"] && $pInfo["rn"] < $info["rn"])
			{
				throw new \Exception("父节点不能是自己的子节点");
			}

			//是否是向左移动
			$toLeft = $info["rn"] - $pInfo["rn"] > 0 ? true : false;

			if ($toLeft)
			{ //向左移动

				//s1 右移中间的左右值

				//要改变左右值的最小边界
				$min = $this->where(["id" => $pgid])
					->value("rn");

				//要改变左右值的最大边界
				$max = $info["ln"];

				//要移动的距离
				$width = $info["width"];

				try
				{
					//右移中间的左值
					$this->where("ln", ">", $min)
						->where("ln", "<", $max)
						->setInc("ln", $width);

					//右移中间的右值
					$this->where("rn", ">=", $min)
						->where("rn", "<", $max)
						->setInc("rn", $width);

					//s2 移动自身

					//要移动的距离
					$distance = $info["ln"] - $pInfo["rn"];

					//左移左值
					$this->where([
						"id" => [
							"IN",
							$groups,
						],
					])
						->setDec("ln", $distance);

					//左移右值
					$this->where([
						"id" => [
							"IN",
							$groups,
						],
					])
						->setDec("rn", $distance);

					//s3 修改自身的父组ID
					$this->isUpdate()
						->save(["pid" => $pgid], ["id" => $gid]);

				}
				catch (\Exception $e)
				{
					Db::rollback();
					throw new \Exception($e->getMessage());
				}

			}
			else
			{ //向右移动

				//s1 左移中间的左右值

				//要改变左右值的最小边界
				$min = $info["rn"] + 1;

				//要改变左右值的最大边界
				$max = $this->where(["id" => $pgid])
						->value("rn") - 1;

				//要移动的距离
				$width = $info["width"];

				try
				{
					//左移中间的左值
					$this->where("ln", ">=", $min)
						->where("ln", "<=", $max)
						->setDec("ln", $width);

					//左移中间的右值
					$this->where("rn", ">=", $min)
						->where("rn", "<=", $max)
						->setDec("rn", $width);

					//s2 移动自身

					//要移动的距离
					$distance = $pInfo["rn"] - $info["rn"] - 1;

					//右移左值
					$this->where([
						"id" => [
							"IN",
							$groups,
						],
					])
						->setInc("ln", $distance);

					//右移右值
					$this->where([
						"id" => [
							"IN",
							$groups,
						],
					])
						->setInc("rn", $distance);

					//s3 修改自身的父组ID
					$this->isUpdate()
						->save(["pid" => $pgid], ["id" => $gid]);

				}
				catch (\Exception $e)
				{
					Db::rollback();
					throw new \Exception($e->getMessage());
				}
			}
		}

		Db::commit();
	}

	/**
	 * 分组列表
	 * @author 王崇全
	 * @date
	 * @param string|null $type    组类型
	 * @param string|null $pgid    父组ID
	 * @param bool        $onlySon 仅包括直接子节点
	 * @return array|false|\PDOStatement|string|\think\Collection
	 * @throws \think\db\exception\DataNotFoundException
	 * @throws \think\db\exception\ModelNotFoundException
	 * @throws \think\exception\DbException
	 */
	public function GetList(string $type = null, string $pgid = null, bool $onlySon = true)
	{
		$map = [];
		if ($type)
		{
			$map["type"] = $type;
		}

		if (isset($pgid))
		{
			$map["pid"] = $pgid;
		}
		else
		{
			$map["pid"] = "";
		}

		$list = $this->field([
			"id",
			"title",
			"type",
		])
			->where($map)
			->order(["ln"])
			->select();
		if (!$list)
		{
			return [];
		}


		$append = [];
		//不仅包括直接子节点
		if (!$onlySon)
		{
			$append[] = "child";
		}

		foreach ($list as &$item)
		{
			$item = $item->append($append)
				->hidden([
					"type",
				])
				->toArray();
		}

		return $list;
	}

	/**
	 * 获取分组信息
	 * @author 王崇全
	 * @date
	 * @param string $gid
	 * @return array
	 * @throws \think\Exception
	 * @throws \think\db\exception\DataNotFoundException
	 * @throws \think\db\exception\ModelNotFoundException
	 * @throws \think\exception\DbException
	 */
	public function Info(string $gid)
	{
		$info = $this->field([
			"pid",
			"title",
		])
			->where(["id" => $gid])
			->find();
		if (!$info)
		{
			return [];
		}

		return $info;
	}

	/**
	 * 数组转树-递归
	 * @author 王崇全
	 * @date
	 * @param $array
	 * @param $pid
	 * @return array
	 */
	public function GetTreeRecursion($array, $pid)
	{
		$tree = [];
		foreach ($array as $v)
		{
			if ($v['pid'] == $pid)
			{
				//父找到子
				$v['child'] = $this->GetTreeRecursion($array, $v['id']);
				$tree[]     = $v;
			}
		}

		return $tree;
	}

	/**
	 * 获取兄弟组的名称
	 * @author 王崇全
	 * @date
	 * @param string $pgid 父组ID
	 * @return array
	 */
	public function GettitlesByPgid(string $pgid = "")
	{
		return $this->where(["pid" => $pgid])
			->column("title");
	}

	/**
	 * 获取组ID集
	 * @author 王崇全
	 * @date
	 * @param string $pgid 父组ID
	 * @return array
	 * @throws \think\db\exception\DataNotFoundException
	 * @throws \think\db\exception\ModelNotFoundException
	 * @throws \think\exception\DbException
	 */
	public function GetIdsByPgid(string $pgid = "")
	{
		$code = $this->field([
			"ln",
			"rn",
		])
			->where([
				"id" => $pgid,
			])
			->find();

		return $this->where([
			"ln" => [
				">=",
				$code["ln"],
			],
			"rn" => [
				"<=",
				$code["rn"],
			],
		])
			->column("id");
	}

	/**
	 * 获取组名称
	 * @author 王崇全
	 * @date
	 * @param string $gid 组ID
	 * @return mixed
	 */
	public function Gettitle(string $gid)
	{
		return $this->where(["id" => $gid])
			->value("title");
	}

	/**
	 * 获取节点信息
	 * @author 王崇全
	 * @date
	 * @param string $gid
	 * @return array|false|\PDOStatement|string|Model
	 * @throws \think\db\exception\DataNotFoundException
	 * @throws \think\db\exception\ModelNotFoundException
	 * @throws \think\exception\DbException
	 */
	protected function GetNodeInfo(string $gid)
	{
		return $this->field([
			"pid",
			"ln",
			"rn",
			"rn - ln + 1 AS width",
		])
			->where(["id" => $gid])
			->find();
	}

	/**
	 * CheckExist
	 * @author 王崇全
	 * @date
	 * @param string $id
	 * @return bool
	 */
	public function CheckExist(string $id)
	{
		return $this->where(["id" => $id])
			->value("id", false) ? true : false;
	}

	/**
	 * 按照路径依次检查组名是否存在
	 * @author 王崇全
	 * @date
	 * @param array $grouptitle 各级分组的名称构成的数组
	 * @return string|null 存在，返回最末级分组的ID；否则，null
	 */
	public function CheckExistBytitle(array $grouptitle)
	{
		$id = null;

		while ($title = array_shift($grouptitle))
		{
			$id = $this->where([
				"title" => $title,
				"pid"   => $id ?? "",
			])
				->value("id", null);

			if (!$id)
			{
				return $id;
			}
		}

		return $id;
	}

	/**
	 * getParentGroupID
	 * @author 王崇全
	 * @date
	 * @param string $id
	 * @return mixed
	 */
	public function getParentGroupID(string $id)
	{
		return $this->where(["id" => $id])
			->value("pid");
	}
}
