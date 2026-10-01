import { reactive } from 'vue';

export function useFormErrors() {
    const errors = reactive({});

    function clear(field) {
        if (field) delete errors[field];
        else Object.keys(errors).forEach(key => delete errors[key]);
    }

    function set(field, message) {
        errors[field] = message;
    }

    function fromResponse(error, fallback, defaultField = '_form') {
        clear();
        const validation = error?.response?.data?.errors;
        if (validation) {
            Object.entries(validation).forEach(([field, messages]) => set(field, Array.isArray(messages) ? messages[0] : messages));
        } else {
            set(defaultField, error?.response?.data?.message || fallback);
        }
        return errors;
    }

    return { errors, clear, set, fromResponse };
}
