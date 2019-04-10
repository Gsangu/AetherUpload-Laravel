<?php

namespace AetherUpload;

class Util
{
    /**
     * The rule of naming a temporary file
     * @return string
     */
    public static function generateTempName()
    {
        return time() . mt_rand(100000, 999999);
    }

    public static function getFileName($baseName, $ext)
    {
        return $baseName . '.' . $ext;
    }

    public static function generateSubDirName()
    {
        switch ( ConfigMapper::get('resource_subdir_rule') ) {
            case "year":
                $name = @date("Y", time());
                break;
            case "month":
                $name = @date("Ym", time());
                break;
            case "date":
                $name = @date("Ymd", time());
                break;
            case "const":
                $name = "subdir";
                break;
            default :
                $name = @date("Ym", time());
                break;
        }

        return $name;
    }

    public static function getDisplayLink($savedPath)
    {
        $storageHost = self::isDistributedWebHost() ? ConfigMapper::get('distributed_deployment_storage_host') : '';

        return $storageHost . ConfigMapper::get('route_display') . '/' . $savedPath;
    }

    public static function getDownloadLink($savedPath, $newName)
    {
        $storageHost = self::isDistributedWebHost() ? ConfigMapper::get('distributed_deployment_storage_host') : '';

        return $storageHost . ConfigMapper::get('route_download') . '/' . $savedPath . '/' . $newName;
    }

    public static function getStorageHostField()
    {
        return new \Illuminate\Support\HtmlString('<input type="hidden" id="aetherupload-storage-host" value="' . (self::isDistributedWebHost() ? ConfigMapper::get('distributed_deployment_storage_host') : '') . '" />');
    }

    public static function isStorageHost()
    {
        return ! ConfigMapper::get('distributed_deployment_enable') || ConfigMapper::get('distributed_deployment_role') === 'storage';
    }

    public static function isDistributedStorageHost()
    {
        return ConfigMapper::get('distributed_deployment_enable') === true && ConfigMapper::get('distributed_deployment_role') === 'storage';
    }

    public static function isDistributedWebHost()
    {
        return ConfigMapper::get('distributed_deployment_enable') === true && ConfigMapper::get('distributed_deployment_role') === 'web';
    }

    public static function getSavedPathKey($group, $hash)
    {
        return $group . '_' . $hash;
    }

    public static function deleteResource($savedPath)
    {
        list($group, $groupSubDir, $name) = explode('_', $savedPath);

        try {
            ConfigMapper::instance()->applyGroupConfig($group);
            $resource = new Resource($group, $groupSubDir, $name);

            return $resource->delete($resource->path);
        }catch (\Exception $e){
            return false;
        }
    }

    public static function deleteRedisSavedPath($savedPath)
    {
        $savedPathArr = explode('_', $savedPath);
        $savedPathKey = $savedPathArr[0].'_'.pathinfo($savedPathArr[2],PATHINFO_FILENAME);

        try {
            return RedisSavedPath::delete($savedPathKey);
        }catch (\Exception $e){
            return false;
        }
    }


}
