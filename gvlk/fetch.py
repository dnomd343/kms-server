#!/usr/bin/env python3

import json
import yaml
import requests
from bs4 import BeautifulSoup

LANG = yaml.full_load(open('config.yml').read())['lang']
URL = 'https://learn.microsoft.com/%s/windows-server/get-started/kms-client-activation-keys'


def fetchGvlks(lang: str) -> dict:  # fetch GVLKs of the specified language
    request = requests.get(URL % lang, timeout = 15)
    request.raise_for_status()  # only http-code 2xx
    request.encoding = 'utf-8'
    content = BeautifulSoup(request.text, 'lxml').select('.content')[0]  # html parsing

    items = [x for x in content.children if x.name in ['h2', 'h3', 'h4', 'table']]  # match target DOMs
    htmlIds = [x['id'] if 'id' in x.attrs else '' for x in items]
    items = items[htmlIds.index('generic-volume-license-keys-gvlk'):]  # located GVLKs section

    gvlks = {}
    for index in range(len(items)):
        if items[index].name == 'table':
            header = items[index - 1]  # last h3/h4 DOM
            table = [x for x in items[index].tbody if x.name == 'tr']  # current table DOM
            text = lambda x: str(x)[4:-5].replace('<br/>', '\n')  # extract DOM text
            gvlks[header['id']] = {
                'name': header.text,  # GVLKs title
                'content': {
                    text(x.select('td')[0]): x.select('td')[1].text for x in table  # extract GVLKs
                }
            }
    return gvlks


def combineGvlks(rawData: dict) -> dict:  # merge multiple languages
    firstVal = lambda x: list(x.values())[0]
    flipDict = lambda x: {v: k for k, v in x.items()}

    def combined(version: str) -> dict:
        keys = [x for _, x in firstVal(rawData)[version]['content'].items()]
        gvlksItem = {
            'name': {lang: data[version]['name'] for (lang, data) in rawData.items()},
            'content': [{'name': {}, 'key': x} for x in keys]
        }
        for index in range(len(keys)):
            for (lang, data) in rawData.items():
                data = flipDict(data[version]['content'])
                gvlksItem['content'][index]['name'][lang] = data[keys[index]]
        return gvlksItem

    return {x: combined(x) for x in list(firstVal(rawData))}


if __name__ == '__main__':
    gvlksData = combineGvlks({x: fetchGvlks(x) for x in LANG})
    with open('raw.json', 'w') as fp:  # output at `raw.json`
        fp.write(json.dumps(gvlksData, indent = 2, ensure_ascii = False) + '\n')
