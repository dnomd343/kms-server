# GVLKs

> Generic Volume License Keys (GVLKs), sometimes referred to as the KMS client keys.

Microsoft provides keys of different Windows versions on the [official website](https://learn.microsoft.com/en-us/windows-server/get-started/kms-client-activation-keys), where data in different languages will be crawled, and json data will be exported after sorting.

Running the following command will automatically crawl and output to `raw.json`, you can specify the language in `config.yml`.

```bash
$ ./fetch.py
```

Since Microsoft basically uses machine translation in other languages, manually repair the content in `raw.json` and save it in `data.json`.

Then run the following command, the final data will be exported, and it will be saved in the `../assets/gvlk/` directory by default. The specific order can be specified in `config.yml`.

```bash
$ ./release.py
```

Because the GVLKs will be updated with Microsoft's iterations, this working directory will always be updated.
