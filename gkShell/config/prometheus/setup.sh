#!/bin/sh
# credit @fvclaus from https://github.com/prometheus/prometheus/issues/2357
set -e

cp /etc/prometheus/prometheus-template.yml /etc/prometheus/prometheus.yml

env |
while IFS='=' read -r name value
do
  # Convert to lowercase: FOO -> foo
  name=$(echo $name | tr '[:upper:]' '[:lower:]')
  # Replace sed seperator character / -> \/
  value=$(echo $value | sed 's;/;\\/;g')
  # Replace occurrences in file.
  sed --in-place=.bak "s/((${name}))/${value}/g" /etc/prometheus/prometheus.yml
done

/bin/prometheus --config.file=/etc/prometheus/prometheus.yml \
  --log.level=debug \
  --storage.tsdb.path=/prometheus \
  --storage.tsdb.retention=200h \
  --web.enable-lifecycle \
  --web.console.libraries=/etc/prometheus/console_libraries \
  --web.console.templates=/etc/prometheus/consoles
