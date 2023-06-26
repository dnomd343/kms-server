#!/usr/bin/env python3

import os
import json
import yaml

Config = yaml.full_load(open('config.yml').read())
AllGvlkData = json.loads(open('data.json').read())


def dumpItem(lang: str, version: str) -> tuple[str, dict]:
    gvlkData = AllGvlkData[version]
    verName = gvlkData['name'][lang]
    return verName, {x['name'][lang]: x['key'] for x in gvlkData['content']}


def dumpGroup(lang: str, versions: str) -> dict:
    result = {}
    for version in versions:
        name, data = dumpItem(lang, version)
        result[name] = data
    return result


def dumpGvlk(lang: str) -> str:
    return json.dumps({
        'win': dumpGroup(lang, Config['win']),
        'win-server': dumpGroup(lang, Config['win-server']),
    }, indent = 2, ensure_ascii = False)


def release(path: str) -> None:
    for lang in Config['lang']:
        with open(os.path.join(path, '%s.json' % lang), 'w') as fp:
            fp.write(dumpGvlk(lang) + '\n')


if __name__ == '__main__':
    if not os.path.exists(Config['path']):
        os.makedirs(Config['path'])
    release(Config['path'])
