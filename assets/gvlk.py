#!/usr/bin/env python3

import json
import requests
from bs4 import BeautifulSoup

LANG = ['en-us', 'zh-cn', 'zh-tw']
URL = 'https://learn.microsoft.com/%s/windows-server/get-started/kms-client-activation-keys'


def analyseKeys(items: list) -> dict:
    def splitHeader(header) -> tuple[str, str]:
        return header['id'], header.text

    def splitTable(table) -> dict:
        dat = {}
        for item in [x for x in table.tbody if x.name == 'tr']:
            name, key = item.select('td')
            dat[str(name)[4:-5].replace('<br/>', '\n')] = key.text
        return dat

    result = {}
    for index in range(len(items)):
        if items[index].name == 'table':
            keyContent = splitTable(items[index])
            keyId, keyName = splitHeader(items[index - 1])
            result[keyId] = {
                'name': keyName,
                'content': keyContent
            }
    return result


def fetchKeys(lang: str) -> dict:
    request = requests.get(URL % lang, timeout = 10)
    request.raise_for_status()
    request.encoding = 'utf-8'
    content = BeautifulSoup(request.text, 'lxml').select('.content')[0]

    items = []
    for element in content.children:
        try:
            if element['id'] == 'generic-volume-license-keys-gvlk':
                items = []  # GVLK record begin
        except: pass
        if element.name in ['h3', 'h4', 'table']:  # match target DOM
            items.append(element)
    return analyseKeys(items)


def combineKeys(rawData: dict) -> dict:
    firstVal = lambda x: list(x.values())[0]
    flipDict = lambda x: {v: k for k, v in x.items()}

    def release(version: str) -> dict:
        keys = [x for _, x in firstVal(rawData)[version]['content'].items()]
        gvlkItem = {
            'name': {lang: data[version]['name'] for (lang, data) in rawData.items()},
            'content': [{'name': {}, 'key': x} for x in keys]
        }
        for index in range(len(keys)):
            for (lang, data) in rawData.items():
                data = flipDict(data[version]['content'])
                gvlkItem['content'][index]['name'][lang] = data[keys[index]]
        return gvlkItem

    result = {}
    for gvlkVersion in list(firstVal(rawData)):
        result[gvlkVersion] = release(gvlkVersion)
    return result


print(json.dumps(
    combineKeys({x: fetchKeys(x) for x in LANG})
))
