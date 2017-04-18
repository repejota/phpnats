all: php python go

.PHONY: php
php:
	@echo
	@echo "PHP Benchmark:"
	@echo "--------------"
	@php --version
	@echo "--------------"
	@cd php && make benchmark

.PHONY: python
python:
	@echo
	@echo "Python Benchmark:"
	@echo "--------------"
	@python --version
	@echo "--------------"
	@cd python && make benchmark

.PHONY: go
go:
	@echo
	@echo "Go Benchmark:"
	@echo "--------------"
	@go version
	@echo "--------------"
	@cd go && make benchmark


