all: go php nodejs python python3

.PHONY: php
php:
	@echo
	@echo "PHP Benchmark:"
	@echo "=============="
	@php --version
	@echo "--------------"
	@cd php && make benchmark

.PHONY: python
python:
	@echo
	@echo "Python Benchmark:"
	@echo "=============="
	@python --version
	@echo "--------------"
	@cd python && make benchmark

.PHONY: python3
python3:
	@echo
	@echo "Python 3 Benchmark:"
	@echo "=============="
	@python3 --version
	@echo "--------------"
	@cd python3 && make benchmark


.PHONY: go
go:
	@echo
	@echo "Go Benchmark:"
	@echo "=============="
	@echo "--------------"
	@go version
	@echo "--------------"
	@cd go && make benchmark

.PHONY: nodejs
nodejs:
	@echo
	@echo "Node JS Benchmark:"
	@echo "=============="
	@node --version
	@echo "--------------"
	@cd nodejs && make benchmark

