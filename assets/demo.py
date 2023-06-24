#!/usr/bin/env python3

import json
import requests
from bs4 import BeautifulSoup

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


ret = fetchKeys('zh-cn')
print(json.dumps(ret))

ret = fetchKeys('en-us')
print(json.dumps(ret))
