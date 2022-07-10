<?php

namespace Yggdrasill\GlobalConfig\App\Http\Controllers;

use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Yggdrasill\GlobalConfig\Models\ConfigPrefix;
use Yggdrasill\GlobalConfig\Support\Facades\GlobalConfig;

class GlobalConfigController extends Controller
{
    use ValidatesRequests;

    /**
     * 获取分组列表、默认/当前分组的配置项
     * @param Request $request
     * @return array
     */
    public function index(Request $request)
    {
        $groups = ConfigPrefix::query()
            ->where('type', ConfigPrefix::TYPE_GROUP)
            ->get()
            ->toArray();

        $ids = Arr::pluck($groups, 'id');
        $configs = GlobalConfig::configsGetByGroupID(...$ids);

        return [
            'groups' => $groups,
            'configs' => $configs,
        ];
    }

    /**
     * 创建分组
     * @param Request $request
     * @return bool
     * @throws \Illuminate\Validation\ValidationException
     */
    public function addGroup(Request $request)
    {
        $params = $this->validate($request, [
            'data' => ['required', 'array'],
            'data.*.key' => ['required', 'string', 'min:1', 'max:255'],
            'data.*.name' => ['required', 'string', 'min:1', 'max:255'],
            'data.*.type' => ['required', 'string', Rule::in([ConfigPrefix::TYPE_GROUP, ConfigPrefix::TYPE_PREFIX])],
        ], [], [
            'data' => '数据',
            'data.*.key' => 'Key',
            'data.*.name' => '语义化名称',
            'data.*.type' => '类型',
        ]);

        return GlobalConfig::groupsAdd(Auth::id(), ...$params);
    }

    /**
     * 更新分组
     * @param Request $request
     * @return bool
     * @throws \Illuminate\Validation\ValidationException
     */
    public function changeGroup(Request $request)
    {
        $params = $this->validate($request, [
            'data' => ['required', 'array'],
            'data.*.id' => ['required', 'int', 'min:1'],
            'data.*.key' => ['required', 'string', 'min:1', 'max:255'],
            'data.*.name' => ['required', 'string', 'min:1', 'max:255'],
            'data.*.delete' => ['required', 'int', 'in:0,1'],
        ], [], [
            'data' => '数据',
            'data.*.id' => 'ID',
            'data.*.key' => 'Key',
            'data.*.name' => '语义化名称',
            'data.*.delete' => '是否删除',
        ]);
        // TODO 删除？

        GlobalConfig::groupsChange(Auth::id(), ...$params);
        return true;
    }

    /**
     * 移除分组
     * @param Request $request
     * @return bool
     * @throws \Illuminate\Validation\ValidationException
     */
    public function deleteGroup(Request $request)
    {
        $params = $this->validate($request, [
            'data' => ['required', 'array'],
            'data.*' => ['required', 'int', 'min:1'],
        ], [], [
            'data' => '数据',
            'data.*' => 'ID',
        ]);

        GlobalConfig::groupsDelete(Auth::id(), ...$params);
        return true;
    }

    /**
     * 创建配置项
     * @param Request $request
     * @return bool
     * @throws \Illuminate\Validation\ValidationException
     */
    public function addConfig(Request $request)
    {
        $params = $this->validate($request, [
            'data' => ['required', 'array'],
            'data.*.key' => ['required', 'string', 'min:1', 'max:255'],
            'data.*.name' => ['required', 'string', 'min:1', 'max:255'],
            'data.*.type' => ['required', 'string', Rule::in([ConfigPrefix::TYPE_GROUP, ConfigPrefix::TYPE_PREFIX])],
        ], [], [
            'data' => '数据',
            'data.*.key' => 'Key',
            'data.*.name' => '语义化名称',
            'data.*.type' => '类型',
            'data.*.value' => '配置值',
        ]);

        GlobalConfig::configsAdd(Auth::id(), ...$params);
        return true;
    }

    /**
     * 更新配置项
     * @param Request $request
     */
    public function changeConfig(Request $request)
    {
        $params = $this->validate($request, [
            'data' => ['required', 'array'],
            'data.*.key' => ['required', 'string', 'min:1', 'max:255'],
            'data.*.name' => ['required', 'string', 'min:1', 'max:255'],
            'data.*.type' => ['required', 'string', Rule::in([ConfigPrefix::TYPE_GROUP, ConfigPrefix::TYPE_PREFIX])],
        ], [], [
            'data' => '数据',
            'data.*.key' => 'Key',
            'data.*.name' => '语义化名称',
            'data.*.type' => '类型',
        ]);
        // TODO 删除？

        GlobalConfig::configsChange(Auth::id(), ...$params);
        return true;
    }

    /**
     * 移除配置项
     * @param Request $request
     */
    public function deleteConfig(Request $request)
    {
        $params = $this->validate($request, [
            'data' => ['required', 'array'],
            'data.*' => ['required', 'int', 'min:1'],
        ], [], [
            'data' => '数据',
            'data.*' => 'ID',
        ]);

        GlobalConfig::configsDelete(Auth::id(), ...$params);
        return true;
    }
}
