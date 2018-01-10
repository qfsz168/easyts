<?php
/**
 * Created by PhpStorm.
 * User: 王崇全
 * Date: 2017/12/16
 * Time: 12:24
 */

namespace app\adm\model;


use think\Facade\Cache;
use think\Model;

class Config extends Model
{

    /**
     * getValue
     * @author 王崇全
     * @date
     * @param string $key
     * @return mixed
     */
    public function getValue(string $key) {
        return $this->cache(1)
            ->where(["key" => $key])
            ->value("value");
    }

    public function getValues(array $keys) {
        return $this->cache(1)
            ->where([
                [
                    "key",
                    "IN",
                    $keys,
                ],
            ])
            ->column("value", "key");
    }

    /**
     * setValue
     * @author 王崇全
     * @date
     * @param string $key
     * @param string $value
     * @return int
     */
    public function setValue(string $key, string $value) {
        if ($key == "admin_pwd") {
            $value = md5(sha1($value)."20171216");
        }

        $this->clearCache();

        return $this->where(["key" => $key])
            ->setField("value", $value);
    }

    /**
     * getList
     * @author 王崇全
     * @date
     * @param string|null $gid
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getList(string $gid = null) {
        $map = [];
        if ($gid) {
            $map["group_id"] = $gid;
        }

        return $this->where($map)
            ->select();
    }

    protected function clearCache() {
        Cache::clear();
    }

}