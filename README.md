# SQLite API

Quick and dirty remote SQLite interaction.

1. Put script in publically-accessible directory alongside SQLite databases
1. Append required database filename as query string, e.g. `http://localhost:8000?database.sqlite`
1. Include SQL statement in request body

Responses are always JSON and in a format appropriate for the statement.
