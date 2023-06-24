#!/usr/bin/env python3

import json
import requests
from bs4 import BeautifulSoup

TIMEOUT = 10
LANG = ['en-us', 'zh-cn', 'zh-tw']
URL = 'https://learn.microsoft.com/%s/windows-server/get-started/kms-client-activation-keys'


def extractKeys(items: list) -> dict:  # detached from original html elements
    def splitHeader(header) -> tuple[str, str]:
        return header['id'], header.text

    def splitTable(table) -> dict:  # split from html table
        dat = {}
        for item in [x for x in table.tbody if x.name == 'tr']:
            name, key = item.select('td')
            dat[str(name)[4:-5].replace('<br/>', '\n')] = key.text
        return dat

    result = {}
    for index in range(len(items)):
        if items[index].name == 'table':
            keyContent = splitTable(items[index])  # GVLK content
            keyId, keyName = splitHeader(items[index - 1])
            result[keyId] = {
                'name': keyName,
                'content': keyContent
            }
    return result


def fetchGvlk(lang: str) -> dict:  # fetch GVLKs of the specified language
    request = requests.get(URL % lang, timeout = TIMEOUT)
    request.raise_for_status()  # only http-code 2xx
    request.encoding = 'utf-8'
    content = BeautifulSoup(request.text, 'lxml').select('.content')[0]  # html parsing

    result = []
    for element in content.children:
        try:
            if element['id'] == 'generic-volume-license-keys-gvlk':
                result = []  # GVLK record begin
        except: pass
        if element.name in ['h3', 'h4', 'table']:  # match target DOM
            result.append(element)
    return extractKeys(result)


def combineGvlk(rawData: dict) -> dict:  # merge multiple languages
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


gvlkData = combineGvlk({x: fetchGvlk(x) for x in LANG})
print(json.dumps(gvlkData))  # output as json format
