# Loan Service – Task #2 Progress

## 📌 Objective

Implement a **Loan Service** that handles loan creation and repayments, ensuring all unit tests (`LoanServiceTest`) pass without modifying the tests themselves.

### Requirements

-   Migrations for:
    -   `scheduled_repayments`
    -   `received_repayments`
    -   (loans table already existed, but required adjustments)
-   Models:
    -   `Loan`
    -   `ScheduledRepayment`
    -   `ReceivedRepayment`
-   `LoanService` class implementing:
    -   `createLoan()`
    -   `repayLoan()`
-   Constraints:
    -   **Should not edit the unit test files**
    -   Ensure compatibility with Laravel 8 and PHPUnit

---

## ✅ Work Completed

1. **Migrations**

    - Created `scheduled_repayments` table with:
        - `loan_id`, `due_date`, `amount`, `outstanding_amount`, `currency_code`, `status`
    - Created `received_repayments` table with:
        - `loan_id`, `received_at`, `amount`, `currency_code`, `notes`
    - Adjusted `loans` table to include:
        - `terms`, `outstanding_amount`, `currency_code`, `processed_at`, `status`

2. **Models**

    - `Loan`:
        - Relations to `User` and `ScheduledRepayment`
        - Status constants (`STATUS_DUE`, `STATUS_REPAID`)
        - Currency constants
    - `ScheduledRepayment`:
        - Belongs to `Loan`
        - Status constants (`STATUS_DUE`, `STATUS_PARTIAL`, `STATUS_REPAID`)
    - `ReceivedRepayment`:
        - Belongs to `Loan`

3. **Factories**

    - Factories implemented for Loan, ScheduledRepayment, and ReceivedRepayment to support unit tests.

4. **LoanService Implementation (in progress)**
    - Logic mapped out from tests:
        - Split loan amount across terms (handle rounding properly)
        - Generate due dates monthly from `processed_at`
        - Create repayments in FIFO order
        - Apply repayments to scheduled repayments (partial/full logic)
        - Update loan status (`due` → `repaid`) when `outstanding_amount=0`

---

## ❌ Issues & Debugging

1. **Column mismatch errors**

    - Initially missing `terms` field in `loans`.
    - Later conflict with `start_date` column (legacy migration).
    - Fixed by adjusting migrations.

2. **Doctrine DBAL dependency**

    - Error: _"Changing columns for table 'loans' requires Doctrine DBAL"_
    - Root cause: migrations used `->change()` with SQLite.
    - Attempted install of DBAL v2.13 caused version conflicts.
    - Solution: should install **doctrine/dbal ^3** as dev dependency:
        ```bash
        composer require --dev doctrine/dbal:^3 --with-all-dependencies
        ```

3. **SQLite limitation**

    - PHPUnit XML still configured for SQLite (`DB_CONNECTION=sqlite`, `DB_DATABASE=:memory:`).
    - Errors: _"SQLite doesn't support multiple calls to dropColumn/renameColumn"_
    - Fix: switch tests to MySQL by updating `phpunit.xml`:
        ```xml
        <server name="DB_CONNECTION" value="mysql"/>
        <server name="DB_DATABASE" value="be_assessment_test"/>
        <server name="DB_USERNAME" value="root"/>
        <server name="DB_PASSWORD" value=""/>
        ```
    - Or create a dedicated `.env.testing` for MySQL.

4. **Current Blocker**
    - Tests are failing because migrations are being applied under **SQLite in-memory DB**, which is incompatible with multiple `dropColumn` / `renameColumn`.
    - Next step: fully migrate test DB to **MySQL**.

---

## 🚀 Next Steps

1. Switch PHPUnit test environment from SQLite to MySQL.
2. Re-run migrations under MySQL test database.
3. Finalize `LoanService` logic (repayment allocation, loan status updates).
4. Ensure all **LoanServiceTest** cases pass:
    - `service can create loan of for a customer`
    - `service can repay a scheduled repayment`
    - `service can repay a scheduled repayment consecutively`
    - `service can repay multiple scheduled repayments`

---

## 📊 Current Status

-   **Migrations**: ✅ done
-   **Models**: ✅ done
-   **Factories**: ✅ done
-   **LoanService**: 🚧 in progress
-   **Tests**: ❌ 4 failed / 0 passed (`LoanServiceTest`)
