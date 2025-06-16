---
applyTo: '**'
---
Coding standards, domain knowledge, and preferences that AI should follow.

Create a Laravel + Livewire 3 application that allows a user to create your workspace,create BD to upload CSV or Excel files, parse them, and store each row as JSON in the database. Display the imported data in a dynamic table with:

- Search across all columns
- Column sorting
- Pagination
- Live updates when new data is imported
- Ability to filter by specific columns
- Ability to export the displayed data as CSV or Excel
- Ability to delete rows
- Ability to edit rows in a modal
- Ability to upload new files and import data
- Ability to view detailed row data in a modal
- Ability to view import history
- Ability to view import statistics (e.g., number of rows imported, errors)
-dashboards with charts showing data statistics and monitoring.
**Project Structure:**

Use `maatwebsite/excel` for file imports. Store rows in a model called `ImportedData`, with a `json` column named `data`.

Use Livewire components for interactive table display and upload form. Make the UI responsive with Tailwind CSS.

**Architecture rules:**
- Business logic must go into dedicated service classes (e.g., `ImportService`)
- Database queries must go through repository classes (e.g., `ImportedDataRepository`)
- Livewire components should remain lightweight and only call services/repositories
- Write tests using Pest for all features: upload, import, search, pagination, Livewire behaviors

Only use Blade and Livewire — do not use Inertia or Vue/React.
