<?php
/*
 * Copyright 2007-2015 Abstrium <contact (at) pydio.com>
 * This file is part of Pydio.
 *
 * Pydio is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Pydio is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with Pydio.  If not, see <http://www.gnu.org/licenses/>.
 *
 * The latest code can be found at <http://pyd.io/>.
 */
namespace Pydio\Plugins\Cache\Doctrine\Ext;
defined('AJXP_EXEC') or die('Access not allowed');

require_once "PatternClearableCache.php";
use Redis;


class PydioRedisCache extends \Doctrine\Common\Cache\RedisCache implements PatternClearableCache
{
    /**
     * @var Redis
     */
    protected $internalRedis;
    protected $internalNamespace;
    protected $internalNamespaceVersion;


    public function setRedis($redis){
        parent::setRedis($redis);
        $this->internalRedis = $redis;
    }

    /**
     * Prefixes the passed id with the configured namespace value.
     *
     * @param string $id The id to namespace.
     *
     * @return string The namespaced id.
     */
    private function getNamespacedId($id)
    {
        // Escape redis MATCH special characters
        $id = str_replace(array("?", "*", "[", "]", "^", "-"), array("\?", "\*", "\[", "\]", "\^", "\-"), $id);
        return sprintf('%s\['.$id.'*', $this->internalNamespace);
    }


    /**
     * @param string $pattern
     * @return bool
     */
    public function deleteKeysStartingWith($pattern)
    {
        $pattern = $this->getNamespacedId($pattern);
        $it = NULL; /* Initialize our iterator to NULL */
        $this->internalRedis->setOption(Redis::OPT_SCAN, Redis::SCAN_RETRY); /* retry when we get no keys back */
        while($arr_keys = $this->internalRedis->scan($it, $pattern)) {
            foreach($arr_keys as $str_key) {
                $this->doDelete($str_key);
            }
        }
    }

    /**
     * @param string $namespace
     * @return void
     */
    public function setNamespace($namespace)
    {
        parent::setNamespace($namespace);
        $this->internalNamespace = $namespace;
    }
}