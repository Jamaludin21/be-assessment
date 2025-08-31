# Backend Assessment - Test #01

## Objective

Create **feature tests** to validate `DebitCard` and `DebitCardTransaction` endpoints along with their policies, validations, and resources.

---

## Business Logic Recap

-   Each customer can own multiple Debit Cards.
-   Each Debit Card can have many Debit Card Transactions.
-   Customers can only access their own debit cards and transactions.

### Endpoints

**Debit Cards:**

-   `GET /debit-cards`
-   `POST /debit-cards`
-   `GET /debit-cards/{debitCard}`
-   `PUT /debit-cards/{debitCard}`
-   `DELETE /debit-cards/{debitCard}`

**Debit Card Transactions (bonus):**

-   `GET /debit-card-transactions?debit_card_id={id}`
-   `POST /debit-card-transactions`
-   `GET /debit-card-transactions/{debitCardTransaction}`

---

## Requirements

-   Verify **positive and negative scenarios**.
-   Assert **response JSON** and **database state**.
-   Ensure **authorization**: customers cannot access or modify resources owned by other users.
-   Use only **Feature tests** (no app code changes).

---

## Current Test Results

Executed with:

```bash
php artisan test --testsuite=Feature
```

### ✅ Passed (8)

-   DebitCardControllerTest › customer can see a list of debit cards
-   DebitCardControllerTest › customer cannot see a list of debit cards of other customers
-   DebitCardControllerTest › customer can create a debit card
-   DebitCardControllerTest › customer cannot see a single debit card details
-   DebitCardControllerTest › customer cannot delete a debit card with transaction
-   DebitCardTransactionControllerTest › customer cannot see a list of debit card transactions of other customer debit card
-   DebitCardTransactionControllerTest › customer cannot create a debit card transaction to other customer debit card
-   DebitCardTransactionControllerTest › customer cannot see a debit card transaction attached to other customer debit card

### ❌ Failed (8)

-   DebitCardControllerTest › customer can see a single debit card details (403 vs 200)
-   DebitCardControllerTest › customer can activate a debit card (403 vs 200)
-   DebitCardControllerTest › customer can deactivate a debit card (403 vs 200)
-   DebitCardControllerTest › customer cannot update a debit card with wrong validation (403 vs 422)
-   DebitCardControllerTest › customer can delete a debit card (403 vs 204)
-   DebitCardTransactionControllerTest › customer can see a list of debit card transactions (403 vs 200)
-   DebitCardTransactionControllerTest › customer can create a debit card transaction (403 vs 201)
-   DebitCardTransactionControllerTest › customer can see a debit card transaction (403 vs 200)

---

## Summary

-   **Total tests:** 16
-   **Passed:** 8
-   **Failed:** 8

The failures are mainly due to **authorization checks returning 403** for valid owner scenarios.  
Next step: adjust **test setup (Gate::before / policy mapping)** to allow legitimate owner access.

---

## Best Practices Followed

-   Used `Passport::actingAs()` for authenticated requests.
-   Asserted both **HTTP status codes** and **database persistence**.
-   Covered both **positive (owner)** and **negative (non-owner)** cases.
-   Validated **JSON response structure**.

---

## Next Action

1. Refine **policy overrides in tests** to correctly differentiate between owner vs non-owner.
2. Re-run the feature suite to confirm all endpoints align with expected business logic.
