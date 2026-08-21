# Release checklist

Publishing a new version of `svg_embed` means updating **three** places: the git repository,
Packagist, and the TER. Packagist happens automatically, the TER does not — it was missed for
release 1.0.0, which left the TER sitting on 0.10.0 (TYPO3 9/10) for years while Packagist was
current. Do not skip step 5.

## 1. Pick the version number

Follow semver. Dropping support for a TYPO3 major version is a **breaking** change and gets a
major bump — that is what turned the TYPO3 13/14 migration into 2.0.0.

Check what is already published before choosing:

```bash
git ls-remote --tags origin
curl -s https://repo.packagist.org/p2/neuedaten/svg-embed.json | python3 -c "import json,sys; [print(v['version']) for v in json.load(sys.stdin)['packages']['neuedaten/svg-embed']]"
```

**A tag that exists on Packagist is burned.** Packagist treats released versions as immutable, so
a number can never be reused — even if the tag was created by mistake or with wrong metadata.

## 2. Bump the version in both files

The two must be identical, otherwise TYPO3 14.2 emits an ext_emconf deprecation during cache
warmup (#108345):

- `composer.json` → `extra."typo3/cms".version`
- `ext_emconf.php` → `version`

If the supported TYPO3 range changed, also update `ext_emconf.php` →
`constraints.depends.typo3`, `composer.json` → `require`, and the requirements table in
`README.md`. The TER reads its "compatible with" badge from `ext_emconf.php`, not from
`composer.json` — that is why the file still ships despite being deprecated in TYPO3 14.2.

## 3. Verify before tagging

```bash
php -l Classes/ViewHelpers/SvgEmbedViewHelper.php && composer validate --strict
```

## 4. Commit, tag and push

Release tags must sit on `master`. Tag names carry the `v` prefix (`v2.0.0`); the TER version
argument does not (`2.0.0`).

```bash
git commit -am "..." && git tag -a vX.Y.Z -m "vX.Y.Z - short summary" && git push origin master && git push origin vX.Y.Z
```

Check what the release archive will contain — the TER artifact is built from this:

```bash
git archive --format=tar vX.Y.Z | tar -t
```

## 5. Packagist — automatic, verify only

The GitHub webhook picks the tag up within seconds. Confirm the constraints actually made it:

```bash
curl -s https://repo.packagist.org/p2/neuedaten/svg-embed.json | python3 -c "import json,sys; v=json.load(sys.stdin)['packages']['neuedaten/svg-embed'][0]; print(v['version'], v.get('require'))"
```

If the hook ever fails, log in at
https://packagist.org/packages/neuedaten/svg-embed and press *Update*.

## 6. TER — manual

The extension key `svg_embed` is registered to `neuedaten`, so no key registration is needed.
The TER accepts version jumps; a skipped release does not have to be uploaded retroactively.

Install tailor globally — **not** as a `--dev` dependency, that would end up in the published
`composer.json`:

```bash
composer global require typo3/tailor
```

Create an access token with scope `extension:write` at https://extensions.typo3.org/ under
Profile → Access Tokens, then publish straight from the GitHub tag so exactly the tagged state
is uploaded:

```bash
TYPO3_API_TOKEN="your-token" ~/.composer/vendor/bin/tailor ter:publish X.Y.Z svg_embed --artefact=https://github.com/neuedaten/svg-embed/archive/refs/tags/vX.Y.Z.zip
```

Verify:

```bash
curl -s https://extensions.typo3.org/api/v1/extension/svg_embed | python3 -c "import json,sys; v=json.load(sys.stdin)[0]['current_version']; print(v['number'], v['typo3_versions'])"
```

The reported TYPO3 majors must match `constraints.depends.typo3` from `ext_emconf.php`.
