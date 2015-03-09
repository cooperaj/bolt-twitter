<?php

namespace Bolt\Extension\Cooperaj\Twitter;

use Doctrine\Common\Cache\Cache;

class ResilientCache implements Cache
{
    /**
     * @var Cache
     */
    private $cache;

    /**
     * @param Cache $cache
     */
    public function __construct(Cache $cache) {
        $this->cache = $cache;
    }

    /**
     * Fetches an entry from the cache.
     *
     * @param string $id The id of the cache entry to fetch.
     * @param $newDataFunction Callable A callback function to allow the fetching of new data if this fails the cached
     *   data will be returned if possible.
     *
     * @return mixed The cached data or FALSE, if no cache entry exists for the given id.
     */
    public function fetch($id)
    {
        if (func_num_args() > 1)
            $newDataCallable = func_get_arg(1);

        // attempt to fetch out of the underlying cache
        if ($cacheObj = $this->cache->fetch($id)) {
            $data = $cacheObj->data;
            $expired = $cacheObj->lifeTime < time();
        }

        if ( ! is_null($data) && ! $expired) // we have a valid cache entry
            return $data;

        if (is_null($newDataCallable)) // called with just the id. behave as if just a cache.
            return false;

        if (is_null($data) || is_null($expired) || $expired) { // if we don't have any data or we have expired data
            try {
                /** @var Callable $newDataCallable */
                $newData = $newDataCallable();
            } catch(\Exception $ex) {}
        }

        if (is_null($newData) && ! is_null($data)) // if we don't have new data but we have old data
            return $data;

        if ( ! is_null($newData))
            return $newData; // we have new data. return it.

        return false;
    }

    /**
     * Tests if an entry exists in the cache.
     *
     * @param string $id The cache id of the entry to check for.
     *
     * @return boolean TRUE if a cache entry exists for the given cache id, FALSE otherwise.
     */
    public function contains($id)
    {
        return $this->cache->contains($id); // if it's in here it'll return it because infinite expiration.
    }

    /**
     * Puts data into the cache.
     *
     * @param string $id The cache id.
     * @param mixed $data The cache entry/data.
     * @param int $lifeTime The cache lifetime.
     *                         If != 0, sets a specific lifetime for this cache entry (0 => infinite lifeTime).
     *
     * @return boolean TRUE if the entry was successfully stored in the cache, FALSE otherwise.
     */
    public function save($id, $data, $lifeTime = 0)
    {
        $cacheObj = new \stdClass();
        $cacheObj->data = $data;

        if ($lifeTime > 0) {
            $cacheObj->lifeTime = time() + $lifeTime;
        }

        return $this->cache->save($id, $cacheObj, 0); // set to an infinite expiration.
    }

    /**
     * Deletes a cache entry.
     *
     * @param string $id The cache id.
     *
     * @return boolean TRUE if the cache entry was successfully deleted, FALSE otherwise.
     */
    public function delete($id)
    {
        return $this->cache->delete($id);
    }

    /**
     * Retrieves cached information from the data store.
     *
     * The server's statistics array has the following values:
     *
     * - <b>hits</b>
     * Number of keys that have been requested and found present.
     *
     * - <b>misses</b>
     * Number of items that have been requested and not found.
     *
     * - <b>uptime</b>
     * Time that the server is running.
     *
     * - <b>memory_usage</b>
     * Memory used by this server to store items.
     *
     * - <b>memory_available</b>
     * Memory allowed to use for storage.
     *
     * @since 2.2
     *
     * @return array|null An associative array with server's statistics if available, NULL otherwise.
     */
    public function getStats()
    {
        return $this->cache->getStats();
    }
}
