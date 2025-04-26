# Opis Zadania
Zadanie rekrutacyjne polegające na stworzeniu systemu do rejestrowania i zarządzania czasem pracy pracowników, umożliwiającego śledzenie przepracowanych godzin, obliczanie wynagrodzenia z uwzględnieniem nadgodzin oraz generowanie miesięcznych podsumowań.

## Technologie
- PHP 8.4
- Symfony 7.2
- Doctrine ORM 3.3
- Doctrine DBAL 3
- MariaDB 10.11.6

## Endpointy API

### Tworzenie pracownika

Starałem się jak najdokładniej odwzorować dane z przykładów.

- **URL:** `/api/v1/employees`
- **Metoda:** `POST`
- **Dane wejściowe:**
  ```json
  {
    "imie": "Karol",
    "nazwisko": "Szabat"
  }
  ```
- **Odpowiedź:**
  ```json
  {
    "id": "019672ca-1753-7944-9482-22d4ce612991"
  }
  ```

### Dodawanie czasu pracy

Starałem się jak najdokładniej odwzorować dane z przykładów.

- **URL:** `/api/v1/work-times`
- **Metoda:** `POST`
- **Dane wejściowe:**
  ```json
  {
    "unikalny_identyfikator_pracownika": "uuid-pracownika",
    "data_i_godzina_rozpoczecia": "26.04.2025 08:00",
    "data_i_godzina_zakonczenia": "26.04.2025 16:00"
  }
  ```
- **Odpowiedź:**
  ```json
  {
    "response": "Czas pracy został dodany!"
  }
  ```

### Pobieranie podsumowania miesięcznego
- **URL:** `/api/v1/work-times/summary`
- **Metoda:** `GET`
- **Parametry:**
    - `employeeId`: UUID pracownika
    - `date`: Format MM-YYYY (np. 04-2023)
- **Odpowiedź:**
  ```json
  {
    "response": {
      "ilosc_normalnych_godzin": 40,
      "stawka": "20,00 PLN",
      "ilosc_nadgodzin": 52,
      "stawka_nadgodzinowa": "40,00 PLN",
      "suma_po_przeliczeniu": "2 880,00 PLN"
    }
  }
  ```

## Konfiguracja
Podstawowe parametry konfiguracyjne znajdują się w pliku `config/services.yaml`:
- `work_time.monthly_norm_hours`: Miesięczna norma godzin pracy
- `work_time.standard_rate_pln`: Standardowa stawka godzinowa w PLN
- `work_time.overtime_rate_multiplier`: Mnożnik stawki za nadgodziny

Ponadto to jest moja przykładowa baza daych z .env
- `DATABASE_URL="mysql://root:@127.0.0.1:3306/Task?serverVersion=10.11.6-MariaDB&charset=utf8mb4"`

## Autor
Kacper Gądek
