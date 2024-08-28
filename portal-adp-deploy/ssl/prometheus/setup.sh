create_dir() {
	mkdir -p $1
	chmod -R 777 $1
	chown -R 1000:1000 $1
}

create_dir "/local/data/grafdata"
create_dir "/local/data/promdata"

