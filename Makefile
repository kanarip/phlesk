lint:
	docker run --rm -t -v $(PWD):/app -w=/app phpstan phpstan analyse --level 1 \
		src/
	phpcs -s src/

stylecheck:
	phpcs -v src/
