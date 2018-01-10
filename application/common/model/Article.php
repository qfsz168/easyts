<?php
/**
 * Created by PhpStorm.
 * User: 王崇全
 * Date: 2017/12/14
 * Time: 18:04
 */

namespace app\common\model;


use think\Model;
use think\model\concern\SoftDelete;

class Article extends Model
{
    //自动时间
    protected $autoWriteTimestamp = "datetime";
    protected $updateTime         = false;

    //软删除
    use SoftDelete;
    protected $deleteTime = 'delete_time';

    protected function initialize() {
        parent::initialize();
    }

    public function getContentAttr($value) {
        return mb_substr(strip_tags($value), 0, 60)."...";
    }

    /**
     * add
     * @author 王崇全
     * @date
     * @param string $title
     * @param string $gid
     * @param string $content
     * @param string $cover
     * @return string
     */
    public function add(string $title, string $gid, string $content, string $cover) {

        $id = uuid();
        $this->insert([
            "id"          => $id,
            "group_id"    => $gid,
            "title"       => $title,
            "cover"       => $cover,
            "content"     => $content,
            "create_time" => date("Y-m-d H:i:s"),
        ]);

        return $id;

    }

    /**
     * edit
     * @author 王崇全
     * @date
     * @param string      $id
     * @param string|null $title
     * @param string|null $gid
     * @param string|null $content
     * @param string|null $cover
     * @return int|string
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function edit(string $id, string $title = null, string $gid = null, string $content = null, string $cover = null) {
        $data = [];
        if (isset($title)) {
            $data["title"] = $title;
        }
        if (isset($gid)) {
            $data["group_id"] = $gid;
        }
        if (isset($content)) {
            $data["content"] = $content;
        }
        if (isset($cover)) {
            $data["cover"] = $cover;
        }

        return db("article")
            ->where("id", $id)
            ->update($data);
    }

    /**
     * getList
     * @author 王崇全
     * @date
     * @param int         $pageNo
     * @param int         $pageSize
     * @param string|null $gid
     * @param int|null    $startTime
     * @param int|null    $endTime
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getList(int $pageNo = 1, int $pageSize = 20, string $gid = null, string $startTime = null, string $endTime = null) {
        $map = [];
        if (isset($gid)) {
            $child = Db("group")
                ->where([
                    [
                        "type",
                        "=",
                        "article",
                    ],
                    [
                        "pid",
                        "=",
                        $gid,
                    ],
                ])
                ->column("id");
            if (!$child) {
                $map[] = [
                    "a.group_id",
                    "=",
                    $gid,
                ];
            } else {
                $map[] = [
                    "a.group_id",
                    "IN",
                    $child,
                ];
            }

        }

        sql_map_region($map, "a.create_time", $startTime, $endTime);

        $count = $this->alias("a")
            ->where($map)
            ->count();
        if (!$count) {
            return [
                [],
                0,
            ];
        }

        $list = $this->alias("a")
            ->field([
                "a.id",
                "a.title",
                "a.cover",
                "a.create_time",
                "a.delete_time",
                "content",
                "g.title" => "group_name",
            ])
            ->join("__GROUP__ g", "a.group_id=g.id", "left")
            ->where($map)
            ->order(["a.create_time" => "desc"])
            ->page($pageNo, $pageSize)
            ->select();

        if (!$list) {
            return [
                [],
                0,
            ];
        }

        foreach ($list as &$v) {
            $v = $v->toArray();
        }

        return [
            $list,
            $count,
        ];
    }

    /**
     * del
     * @author 王崇全
     * @date
     * @param string $id
     * @return int
     */
    public function del(string $id) {
        return $this->where([
            "id" => $id,
        ])
            ->delete();
    }


    /**
     * getInfo
     * @author 王崇全
     * @date
     * @param string $id
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getInfo(string $id) {
        return $this->alias("a")
            ->field([
                "a.id",
                "a.group_id",
                "a.title",
                "a.content",
                "a.create_time",
            ])
            ->where("a.id", $id)
            ->find();
    }

    /**
     * getCover
     * @author 王崇全
     * @date
     * @param string $id
     * @return mixed
     */
    public function getCover(string $id) {
        return $this->where("id", $id)
            ->value("cover");
    }

    /**
     * getCount
     * @author 王崇全
     * @date
     * @param string $gid
     * @return int|string
     */
    public function getCount(string $gid) {
        return $this->where("group_id", $gid)
            ->count();
    }

    /**
     * getFirst
     * @author 王崇全
     * @date
     * @param string $gid
     * @return mixed
     */
    public function getFirst(string $gid) {
        return $this->where("group_id", $gid)
            ->order(["create_time" => "DESC"])
            ->value("id");
    }

}