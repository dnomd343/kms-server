#!/usr/bin/env python3

import json
import yaml

GvlkData = json.loads(open('data.json').read())
Config = yaml.full_load(open('config.yml').read())


def dumpGvlks(language: str, versionList: list) -> dict:
    result = {}
    for version in versionList:
        gvlkData = GvlkData[version]
        result[gvlkData['name'][language]] = {
            x['name'][language]: x['key'] for x in gvlkData['content']
        }
    return result


if __name__ == '__main__':
    data = {lang: {
        'win': dumpGvlks(lang, Config['win']),
        'win-server': dumpGvlks(lang, Config['win-server']),
    } for lang in Config['lang']}
    with open(Config['path'], 'w') as fp:
        fp.write(json.dumps(data, indent = 2, ensure_ascii = False) + '\n')
