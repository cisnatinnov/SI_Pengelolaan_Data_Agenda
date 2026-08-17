import { reactive } from 'vue';

/**
 * Lightweight responsive form validation.
 *
 * `rules` maps a field key to a function that returns an error message string
 * when invalid, or null when the field is valid. Fields are revalidated on
 * every input event, so errors update live while the user types.
 */
export function useFormValidation(rules) {
    const errors = reactive({});

    const validateField = (key) => {
        const rule = rules[key];
        if (!rule) return null;

        const error = rule();
        if (error) {
            errors[key] = error;
        } else {
            delete errors[key];
        }

        return error;
    };

    /**
     * Validate every rule. Returns the key of the first invalid field,
     * or null when the whole form is valid.
     */
    const validateAll = () =>
        Object.keys(rules).find((key) => validateField(key)) ?? null;

    /**
     * Responsive revalidation: runs the field's rule on every input so
     * errors appear/clear while the user types.
     */
    const onInput = (key) => {
        validateField(key);
    };

    /**
     * Shared input/select/textarea styling, highlighting invalid fields.
     */
    const fieldClass = (key) =>
        errors[key]
            ? 'w-full rounded-xl border border-red-500 bg-white  px-3 py-2 text-sm focus:border-red-500 focus:ring-red-500 focus:ring-1 outline-none '
            : 'w-full rounded-xl border border-slate-300  bg-white  px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500 focus:ring-1 outline-none ';

    return { errors, validateAll, onInput, fieldClass };
}
